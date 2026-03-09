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
use Illuminate\Support\Facades\Log;

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
            ->when($request->filled('search'), function($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('company_name', 'like', "%{$request->search}%")
                  ->orWhere('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%");
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    WHEN status = 'query' THEN 3
                    WHEN status = 'successful' THEN 4
                    ELSE 99
                END
            ")
            ->orderByDesc('submission_date')
            ->paginate(10)
            ->withQueryString();

        // Load active service and its fields
        $service = Service::where('name', $serviceKey)
            ->where('is_active', true)
            ->with(['fields' => function($q) {
                $q->where('is_active', true);
            }, 'prices'])
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
        
        // Check if user account is active
        if (($user->status ?? 'inactive') !== 'active') {
            return redirect()->back()
                ->with('error', "Your account is currently " . ($user->status ?? 'inactive') . ". Access denied.")
                ->withInput();
        }

        // Validate service field
        $request->validate([
            'service_field_id' => 'required|exists:service_fields,id'
        ]);
        
        $serviceField = ServiceField::with(['service', 'prices'])->findOrFail($request->service_field_id);
        
        // Validation rules
        $rules = [
            // Business Information
            'business_type' => 'required|string|max:255',
            'nature_of_business' => 'required|string|max:255',
            'business_name_1' => 'required|string|max:255',
            'business_name_2' => 'nullable|string|max:255',
            
            // Director 1 Personal Information
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|in:Male,Female,Other',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            
            // Director 1 Residential Address
            'state' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'house_number' => 'required|string|max:255',
            'street_name' => 'required|string|max:255',
            'res_description' => 'nullable|string|max:500',

            // Business Address
            'bus_state' => 'required|string|max:255',
            'bus_lga' => 'required|string|max:255',
            'bus_city' => 'required|string|max:255',
            'bus_house_no' => 'required|string|max:255',
            'bus_street' => 'required|string|max:255',
            'bus_description' => 'nullable|string|max:500',

            // Director 1 Uploads
            'nin_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'signature_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_upload' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Director 2 (Optional)
            'director2_surname' => 'nullable|string|max:255',
            'director2_first_name' => 'nullable|string|max:255',
            'director2_other_name' => 'nullable|string|max:255',
            'director2_phone_number' => 'nullable|string|max:20',
            'director2_gender' => 'nullable|string|in:Male,Female,Other',
            'director2_date_of_birth' => 'nullable|date',
            'director2_email' => 'nullable|email|max:255',
            'director2_res_state' => 'nullable|string|max:255',
            'director2_res_lga' => 'nullable|string|max:255',
            'director2_res_city' => 'nullable|string|max:255',
            'director2_res_house_no' => 'nullable|string|max:255',
            'director2_res_street' => 'nullable|string|max:255',
            'director2_res_description' => 'nullable|string|max:500',
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

        // Check wallet
        $wallet = Wallet::where('user_id', $user->id)->first();
        
        if (!$wallet) {
            return back()->with('error', 'Wallet not found. Please contact support.')->withInput();
        }
        
        if ($wallet->status !== 'active') {
            return back()->with('error', 'Your wallet is not active.')->withInput();
        }

        if ($wallet->balance < $servicePrice) {
            return back()->with('error', 'Insufficient balance. Please fund your wallet.')->withInput();
        }

        // Generate reference
        $reference = 'CAC' . date('ymd') . strtoupper(substr(uniqid(), -6));
        $performedBy = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? $user->surname ?? ''));

        DB::beginTransaction();

        try {
            // 1. Charge Wallet
            $wallet->decrement('balance', $servicePrice);

            // 2. Handle File Uploads
            $uploads = [];
            
            // Director 1 Uploads
            if ($request->hasFile('nin_upload')) {
                $uploads['nin'] = $request->file('nin_upload')->store('uploads/cac/nin', 'public');
            }
            if ($request->hasFile('signature_upload')) {
                $uploads['signature'] = $request->file('signature_upload')->store('uploads/cac/signature', 'public');
            }
            if ($request->hasFile('passport_upload')) {
                $uploads['passport'] = $request->file('passport_upload')->store('uploads/cac/passport', 'public');
            }

            // Director 2 Uploads (if provided)
            if ($request->hasFile('director2_nin_upload')) {
                $uploads['director2_nin'] = $request->file('director2_nin_upload')->store('uploads/cac/nin', 'public');
            }
            if ($request->hasFile('director2_signature_upload')) {
                $uploads['director2_signature'] = $request->file('director2_signature_upload')->store('uploads/cac/signature', 'public');
            }
            if ($request->hasFile('director2_passport_upload')) {
                $uploads['director2_passport'] = $request->file('director2_passport_upload')->store('uploads/cac/passport', 'public');
            }

            // Prepare all form data (excluding files and token)
            $formData = $request->except([
                '_token', 
                'nin_upload', 
                'signature_upload', 
                'passport_upload',
                'director2_nin_upload', 
                'director2_signature_upload', 
                'director2_passport_upload',
                'service_field_id'
            ]);
            
            // Add uploads to the data
            $formData['uploads'] = $uploads;

            // Prepare director 2 address as string for storage
            $director2Address = null;
            if ($request->filled('director2_res_house_no') || $request->filled('director2_res_street')) {
                $director2Address = trim(
                    $request->director2_res_house_no . ' ' . 
                    $request->director2_res_street . ', ' . 
                    $request->director2_res_city . ', ' . 
                    $request->director2_res_lga . ', ' . 
                    $request->director2_res_state
                );
            }

            // 3. Create Transaction Record
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
                    'details' => $formData,
                ]),
            ]);

            // 4. Create AgentService Record
            $agentService = AgentService::create([
                'reference'           => $reference,
                'user_id'             => $user->id,
                'service_id'          => $serviceField->service_id,
                'service_field_id'    => $serviceField->id,
                'service_name'        => 'CAC',
                'service_type'        => 'CAC',
                'field_name'          => $serviceField->field_name,
                'amount'              => $servicePrice,
                'performed_by'        => $performedBy,
                'transaction_id'      => $transaction->id,
                'submission_date'     => now(),
                'status'              => 'pending',
                
                // Personal Information (Director 1)
                'first_name'          => $request->first_name,
                'last_name'           => $request->surname,
                'middle_name'         => $request->other_name,
                'email'               => $request->email,
                'phone_number'        => $request->phone_number,
                'dob'                 => $request->date_of_birth,
                'gender'              => $request->gender,
                
                // Residential Address (Director 1)
                'state'               => $request->state,
                'lga'                 => $request->lga,
                'city'                => $request->city,
                'house_number'        => $request->house_number,
                'street_name'         => $request->street_name,
                
                // Business Information
                'company_name'        => $request->business_name_1,
                'company_type'        => $request->business_type,
                'description'         => $request->nature_of_business,

                // Business Address
                'business_state'        => $request->bus_state,
                'business_lga'          => $request->bus_lga,
                'business_city'         => $request->bus_city,
                'business_house_number' => $request->bus_house_no,
                'business_street'       => $request->bus_street,
                'business_description'  => $request->bus_description,
                
                // Director 2 Information (if provided)
                'director2_surname'    => $request->director2_surname,
                'director2_first_name' => $request->director2_first_name,
                'director2_middle_name'=> $request->director2_other_name,
                'director2_phone'      => $request->director2_phone_number,
                'director2_gender'     => $request->director2_gender,
                'director2_dob'        => $request->director2_date_of_birth,
                'director2_email'      => $request->director2_email,
                'director2_address'    => $director2Address,
                
                // Store all data in JSON field for flexibility
                'field'               => json_encode($formData),
            ]);

            DB::commit();

            // Log successful submission
            Log::info('CAC Registration successful', [
                'user_id' => $user->id,
                'reference' => $reference,
                'amount' => $servicePrice
            ]);

            // Redirect with success message
            return redirect()->route('cac.index')->with([
                'status' => 'success',
                'message' => 'CAC Registration submitted successfully. Reference: ' . $reference
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('CAC submission failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Delete uploaded files if any (optional cleanup)
            if (isset($uploads)) {
                foreach ($uploads as $file) {
                    Storage::disk('public')->delete($file);
                }
            }
            
            return back()
                ->with('error', 'An error occurred while processing your request. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show a specific CAC registration
     */
    public function show($reference)
    {
        $user = Auth::user();
        
        $submission = AgentService::with(['transaction', 'user'])
            ->where('user_id', $user->id)
            ->where('reference', $reference)
            ->where('service_name', 'CAC')
            ->firstOrFail();
        
        return view('pages.dashboard.cac.show', [
            'submission' => $submission
        ]);
    }

    /**
     * Download uploaded file
     */
    public function downloadFile($reference, $fileType)
    {
        $user = Auth::user();
        
        $submission = AgentService::where('user_id', $user->id)
            ->where('reference', $reference)
            ->where('service_name', 'CAC')
            ->firstOrFail();
        
        $field = $submission->field;

        if (is_string($field)) {
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $field = $decoded;
            }
        } elseif ($field instanceof \Illuminate\Contracts\Support\Arrayable) {
            $field = $field->toArray();
        } elseif (is_object($field)) {
            $field = (array) $field;
        }
        
        $filePath = null;
        
        switch ($fileType) {
            case 'nin':
                $filePath = $field['uploads']['nin'] ?? null;
                break;
            case 'signature':
                $filePath = $field['uploads']['signature'] ?? null;
                break;
            case 'passport':
                $filePath = $field['uploads']['passport'] ?? null;
                break;
            case 'director2_nin':
                $filePath = $field['uploads']['director2_nin'] ?? null;
                break;
            case 'director2_signature':
                $filePath = $field['uploads']['director2_signature'] ?? null;
                break;
            case 'director2_passport':
                $filePath = $field['uploads']['director2_passport'] ?? null;
                break;
        }
        
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return Storage::disk('public')->download($filePath);
    }
}