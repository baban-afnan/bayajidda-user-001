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

class VisaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch Visa Service
        $visaService = Service::with(['fields' => function ($query) {
            $query->where('is_active', 1);
        }])
            ->where('name', 'VISA')
            ->first();

        $query = AgentService::where('user_id', $user->id)
            ->where('service_type', 'visa');

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

        $role = $user->role ?? 'user';
        
        return view('visa.index', compact(
            'submissions',
            'visaService',
            'wallet',
            'role'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_field'    => 'required|exists:service_fields,id',
            'country_apply'   => 'required|string|max:255',
            'applicant_class'  => 'required|in:Adult,Child,Infant',
            'gender'           => 'required|in:Male,Female',
            'email'            => 'required|email|max:255',
            'phone_number'     => 'required|string|max:20',
            'passport_file'    => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo_file'       => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'description'      => 'nullable|string|max:1000',
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

        $totalAmount = $serviceField->prices()
            ->where('user_type', $role)
            ->value('price') ?? $serviceField->base_price;

        if ($wallet->balance < $totalAmount) {
            $msg = "Insufficient wallet balance. Required: NGN " . number_format($totalAmount, 2);
            return redirect()->route('visa.index')->withErrors(['wallet' => $msg])->withInput();
        }

        DB::beginTransaction();

        try {
            // Handle International Passport upload
            $passportUrl = null;
            if ($request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $fileName = 'visa_passport_' . Str::slug($user->email) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/visas/passports', $fileName, 'public');
                $passportUrl = asset('storage/' . $path);
            }

            // Handle Passport Photo upload
            $photoUrl = null;
            if ($request->hasFile('photo_file')) {
                $file = $request->file('photo_file');
                $fileName = 'visa_photo_' . Str::slug($user->email) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/visas/photos', $fileName, 'public');
                $photoUrl = asset('storage/' . $path);
            }

            // Debit wallet
            $wallet->decrement('balance', $totalAmount);

            $transactionRef = 'VSA' . date('ymdHis') . strtoupper(Str::random(4));
            $performedBy = trim("{$user->first_name} {$user->last_name}");

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'description' => "Visa application: {$serviceField->field_name} (Applying to {$validated['country_apply']})",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $service->name,
                    'service_field' => $serviceField->field_name,
                    'country_apply' => $validated['country_apply'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'applicant_class' => $validated['applicant_class'],
                    'gender' => $validated['gender'],
                    'passport_url' => $passportUrl,
                    'photo_url' => $photoUrl,
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
                'country' => $validated['country_apply'],
                'applicant_class' => $validated['applicant_class'],
                'gender' => $validated['gender'],
                'passport_url' => $passportUrl,
                'file_url' => $photoUrl, // Using file_url for the second upload
                'description' => $validated['description'] ?? "Visa application for {$validated['country_apply']}",
                'amount' => $totalAmount,
                'transaction_id' => $transaction->id,
                'submission_date' => now(),
                'status' => 'pending',
                'service_type' => 'visa',
                'performed_by' => $performedBy,
            ]);

            DB::commit();

            return redirect()->route('visa.index')->with([
                'status' => 'success',
                'message' => "Visa application submitted successfully. Charged: NGN " . number_format($totalAmount, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Visa Application Error: ' . $e->getMessage());
            return redirect()->route('visa.index')->withErrors([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ])->withInput();
        }
    }
}
