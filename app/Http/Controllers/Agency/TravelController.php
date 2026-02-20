<?php

namespace App\Http\Controllers\Agency;

use App\Models\ServiceField;
use App\Models\AgentService;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class TravelController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch Travel Service
        $travelService = Service::with(['fields' => function ($query) {
            $query->where('is_active', 1);
        }])
            ->where('name', 'TRAVEL')
            ->first();

        $query = AgentService::where('user_id', $user->id)
            ->where('service_type', 'travel');

        // Apply optional filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginate results
        $submissions = $query->orderByDesc('submission_date')
            ->paginate(10)
            ->withQueryString();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Prices for the user based on role
        $role = $user->role ?? 'user';
        
        // Return view with data
        return view('travel.index', compact(
            'submissions',
            'travelService',
            'wallet',
            'role'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_field'   => 'required|exists:service_fields,id',
            'from_country'    => 'required|string|max:255',
            'to_country'      => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone_number'    => 'required|string|max:20',
            'visa_type'       => 'nullable|string|max:255',
            'applicant_class' => 'required|in:Adult,Child,Infant',
            'gender'          => 'required|in:Male,Female',
            'departure_date'  => 'required|date|after_or_equal:today',
            'return_date'     => 'nullable|required_if:trip_type,round_trip|date|after_or_equal:departure_date',
            'trip_type'       => 'required|in:one_way,round_trip',
            'passport_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
            'description'     => 'required|string|max:1000',
        ]);

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->status !== 'active') {
            return back()->with([
                'status' => 'error',
                'message' => 'Your wallet is not active.',
            ])->withInput();
        }

        $serviceField = ServiceField::findOrFail($validated['service_field']);
        $service = $serviceField->service;

        $role = $user->role ?? 'user';

        // Calculate price based on role
        $totalAmount = $serviceField->prices()
            ->where('user_type', $role)
            ->value('price') ?? $serviceField->base_price;

        if ($wallet->balance < $totalAmount) {
            $msg = "Insufficient wallet balance. Required: NGN " . number_format($totalAmount, 2);
            return redirect()->route('travel.index')->withErrors(['wallet' => $msg])->withInput();
        }

        DB::beginTransaction();

        try {
            // Handle passport upload
            $passportUrl = null;
            if ($request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $fileName = 'passport_' . Str::slug($user->email) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/passports', $fileName, 'public');
                $passportUrl = asset('storage/' . $path);
            }

            // Debit wallet
            $wallet->decrement('balance', $totalAmount);

            $transactionRef = 'TRV' . date('ymdHis') . strtoupper(Str::random(4));
            $performedBy = trim("{$user->first_name} {$user->last_name}");

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'description' => "Travel application: {$serviceField->field_name} ({$validated['from_country']} to {$validated['to_country']})",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $service->name,
                    'service_field' => $serviceField->field_name,
                    'from_country' => $validated['from_country'],
                    'to_country' => $validated['to_country'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'departure_date' => $validated['departure_date'],
                    'return_date' => $validated['return_date'],
                    'trip_type' => $validated['trip_type'],
                    'visa_type' => $validated['visa_type'],
                    'applicant_class' => $validated['applicant_class'],
                    'gender' => $validated['gender'],
                    'passport_url' => $passportUrl,
                ],
            ]);

            // Store submission in AgentService
            AgentService::create([
                'reference' => $transactionRef,
                'user_id' => $user->id,
                'service_id' => $service->id,
                'service_field_id' => $serviceField->id,
                'service_name' => $service->name,
                'field_code' => $serviceField->field_code,
                'service_field_name' => $serviceField->field_name,
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'country' => $validated['from_country'],
                'from_country' => $validated['from_country'],
                'to_country' => $validated['to_country'],
                'departure_date' => $validated['departure_date'],
                'return_date' => $validated['return_date'],
                'trip_type' => $validated['trip_type'],
                'visa_type' => $validated['visa_type'],
                'applicant_class' => $validated['applicant_class'],
                'gender' => $validated['gender'],
                'passport_url' => $passportUrl,
                'description' => $validated['description'],
                'amount' => $totalAmount,
                'transaction_id' => $transaction->id,
                'submission_date' => now(),
                'status' => 'pending',
                'service_type' => 'travel',
                'performed_by' => $performedBy,
            ]);




            DB::commit();

            return redirect()->route('travel.index')->with([
                'status' => 'success',
                'message' => "Travel application submitted successfully. Charged: NGN " . number_format($totalAmount, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Travel Application Error: ' . $e->getMessage());
            return redirect()->route('travel.index')->withErrors([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    public function getServiceFields($serviceId)
    {
        $role = auth()->user()->role ?? 'user';

        $fields = ServiceField::where('service_id', $serviceId)
            ->where('is_active', 1)
            ->get();

        $mappedFields = $fields->map(function ($field) use ($role) {
            $price = $field->prices()->where('user_type', $role)->value('price') ?? $field->base_price;
            return [
                'id' => $field->id,
                'field_name' => $field->field_name,
                'description' => $field->description,
                'price' => $price,
            ];
        });

        return response()->json($mappedFields);
    }
}
