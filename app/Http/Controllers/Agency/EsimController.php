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

class EsimController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch Esim Service
        $esimService = Service::with(['fields' => function ($query) {
            $query->where('is_active', 1);
        }])
            ->where('name', 'Esim')
            ->first();

        $query = AgentService::where('user_id', $user->id)
            ->where('service_type', 'esim');

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
        
        return view('agency.esim.index', compact(
            'submissions',
            'esimService',
            'wallet',
            'role'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_field'    => 'required|exists:service_fields,id',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'middle_name'      => 'nullable|string|max:255',
            'email'            => 'required|email|max:255',
            'phone_number'     => 'required|string|max:20',
            'nin'              => 'required|string|size:11|regex:/^[0-9]{11}$/',
            'address'          => 'required|string|max:1000',
            'photo_file'       => 'required|file|mimes:jpg,jpeg,png|max:5120',
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
            return redirect()->route('esim.index')->withErrors(['wallet' => $msg])->withInput();
        }

        DB::beginTransaction();

        try {
            // Handle Photo upload (White background)
            $photoUrl = null;
            if ($request->hasFile('photo_file')) {
                $file = $request->file('photo_file');
                $fileName = 'esim_photo_' . Str::slug($user->email) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/esim/photos', $fileName, 'public');
                $photoUrl = asset('storage/' . $path);
            }

            // Debit wallet with row lock to prevent race conditions
            $walletLock = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            $walletLock->decrement('balance', $totalAmount);

            $transactionRef = 'ESM' . date('ymdHis') . strtoupper(Str::random(4));
            $performedBy = trim("{$user->first_name} {$user->last_name}");
            
            $fullName = trim("{$validated['first_name']} {$validated['middle_name']} {$validated['last_name']}");

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'description' => "ESIM request: {$serviceField->field_name}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $service->name,
                    'service_field' => $serviceField->field_name,
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'nin' => $validated['nin'],
                    'full_name' => $fullName,
                    'address' => $validated['address'],
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
                
                // Mapped Requested explicit fields
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'nin' => $validated['nin'],
                'description' => $validated['address'], // Address maps to Description text block
                'passport_url' => $photoUrl,            // Photo maps to Passport_URL string

                'amount' => $totalAmount,
                'transaction_id' => $transaction->id,
                'submission_date' => now(),
                'status' => 'pending',
                'service_type' => 'esim',
                'performed_by' => $performedBy,
            ]);

            DB::commit();

            return redirect()->route('esim.index')->with([
                'status' => 'success',
                'message' => "ESIM application submitted successfully. Charged: NGN " . number_format($totalAmount, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ESIM Application Error: ' . $e->getMessage());
            return redirect()->route('esim.index')->withErrors([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ])->withInput();
        }
    }
}
