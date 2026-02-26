<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;

class CacRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $serviceKey = 'CAC'; // Assuming the service name in DB is 'CAC'

        // Query submissions
        $submissions = AgentService::with('transaction')
            ->where('user_id', $user->id)
            ->where('service_name', $serviceKey)
            ->when($request->filled('search'), fn($q) =>
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('business_name', 'like', "%{$request->search}%")) // Assuming business_name might be stored or searched in JSON? simpler to search ref
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    WHEN status = 'query' THEN 3
                    WHEN status = 'successful' THEN 4
                    ELSE 99
                END
            ")->orderByDesc('submission_date')
            ->paginate(10)
            ->withQueryString();

        // Load active service and its fields
        $service = Service::where('name', $serviceKey)
            ->where('is_active', true)
            ->with(['fields' => fn($q) => $q->where('is_active', true), 'prices'])
            ->first();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $fields = $service?->fields ?? collect();
        $prices = $service?->prices ?? collect();

        return view('pages.dashboard.cac.index', [
            'fields'        => $fields,
            'service'       => $service,
            'submissions'   => $submissions,
            'servicePrices' => $prices,
            'wallet'        => $wallet,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (($user->status ?? 'inactive') !== 'active') {
             return redirect()->back()->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.");
        }

        // 1. Validation
        $request->validate(['service_field_id' => 'required|exists:service_fields,id']);
        $serviceField = ServiceField::with(['service', 'prices'])->findOrFail($request->service_field_id);
        
        $rules = [
            'business_type' => 'required|string',
            'nature_of_business' => 'required|string',
            'business_name_1' => 'required|string',
            'business_name_2' => 'nullable|string',
            
            // Director 1
            'surname' => 'required|string',
            'first_name' => 'required|string',
            'other_name' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
            
            // Director 1 Address
            'res_state' => 'required|string',
            'res_lga' => 'required|string',
            'res_city' => 'required|string',
            'res_house_no' => 'required|string',
            'res_street' => 'required|string',
            'res_description' => 'nullable|string',

            // Business Address
            'bus_state' => 'required|string',
            'bus_lga' => 'required|string',
            'bus_city' => 'required|string',
            'bus_house_no' => 'required|string',
            'bus_street' => 'required|string',
            'bus_description' => 'nullable|string',

            // Director 1 Uploads
            'nin_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'signature_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Director 2 (Optional)
            'director2_surname' => 'nullable|string',
            'director2_first_name' => 'nullable|string',
            'director2_other_name' => 'nullable|string',
            'director2_phone_number' => 'nullable|string',
            'director2_gender' => 'nullable|string',
            'director2_date_of_birth' => 'nullable|date',
            'director2_res_state' => 'nullable|string',
            'director2_res_lga' => 'nullable|string',
            'director2_res_city' => 'nullable|string',
            'director2_res_house_no' => 'nullable|string',
            'director2_res_street' => 'nullable|string',
            'director2_nin_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'director2_signature_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'director2_passport_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
        $request->validate($rules);

        // Determine price
        $servicePrice = $serviceField->prices
            ->where('user_type', $user->role)
            ->first()?->price ?? $serviceField->base_price;

        if ($servicePrice === null) {
            return back()->with('error', 'Service price not configured.')->withInput();
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        if ($wallet->status !== 'active') {
            return back()->with('error', 'Your wallet is not active.')->withInput();
        }

        if ($wallet->balance < $servicePrice) {
            return back()->with('error', 'Insufficient balance.')->withInput();
        }

        $reference = 'CAC' . date('ymd') . strtoupper(substr(uniqid(), -5));
        $performedBy = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? $user->surname ?? ''));

        DB::beginTransaction();

        try {
            // 2. Charge Wallet First
            $wallet->decrement('balance', $servicePrice);

            // 3. Handle Uploads
            $uploads = [];
            if ($request->hasFile('nin_upload')) {
                $uploads['nin'] = $request->file('nin_upload')->store('uploads/cac/nin', 'public');
            }
            if ($request->hasFile('signature_upload')) {
                $uploads['signature'] = $request->file('signature_upload')->store('uploads/cac/signature', 'public');
            }
            if ($request->hasFile('passport_upload')) {
                $uploads['passport'] = $request->file('passport_upload')->store('uploads/cac/passport', 'public');
            }

            if ($request->hasFile('director2_nin_upload')) {
                $uploads['director2_nin'] = $request->file('director2_nin_upload')->store('uploads/cac/nin', 'public');
            }
            if ($request->hasFile('director2_signature_upload')) {
                $uploads['director2_signature'] = $request->file('director2_signature_upload')->store('uploads/cac/signature', 'public');
            }
            if ($request->hasFile('director2_passport_upload')) {
                $uploads['director2_passport'] = $request->file('director2_passport_upload')->store('uploads/cac/passport', 'public');
            }

            // Merge uploads into the field data for easier retrieval
            $fullData = $request->except(['_token', 'nin_upload', 'signature_upload', 'passport_upload', 'director2_nin_upload', 'director2_signature_upload', 'director2_passport_upload']);
            $fullData['uploads'] = $uploads;

            // 4. Create Transaction Record
            $transaction = Transaction::create([
                'transaction_ref' => $reference,
                'user_id'         => $user->id,
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'description'     => "CAC Registration - {$serviceField->field_name}",
                'type'            => 'debit',
                'status'          => 'completed',
                'metadata'        => json_encode([
                    'service' => 'CAC',
                    'field' => $serviceField->field_name,
                    'details' => $fullData,
                ]),
            ]);

            // 5. Create AgentService Record
            AgentService::create([
                'reference'       => $reference,
                'user_id'         => $user->id,
                'service_id'      => $serviceField->service_id,
                'service_field_id'=> $serviceField->id,
                'service_name'    => 'CAC',
                'field_name'      => $serviceField->field_name,
                'amount'          => $servicePrice,
                'performed_by'    => $performedBy,
                'transaction_id'  => $transaction->id,
                'submission_date' => now(),
                'status'          => 'pending',
                'service_type'    => 'CAC',
                'first_name'      => $request->first_name,
                'last_name'       => $request->surname,
                'middle_name'     => $request->other_name,
                'email'           => $request->email,
                'phone_number'    => $request->phone_number,
                'dob'             => $request->date_of_birth,
                'gender'          => $request->gender,
                'state'           => $request->res_state,
                'lga'             => $request->res_lga,
                'city'            => $request->res_city,
                'house_number'    => $request->res_house_no,
                'street_name'     => $request->res_street,
                'company_name'    => $request->business_name_1,
                'company_type'    => $request->business_type,
                'field'           => json_encode($fullData),
            ]);

            DB::commit();

            return redirect()->route('cac.index')->with([
                'status' => 'success',
                'message' => 'CAC Registration submitted successfully. Reference: ' . $reference
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('CAC submission failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'reference' => $reference,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }
}
