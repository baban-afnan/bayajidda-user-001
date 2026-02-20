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

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch Hotel Service
        $hotelService = Service::with(['fields' => function ($query) {
            $query->where('is_active', 1);
        }])
            ->where('name', 'HOTEL')
            ->first();

        $query = AgentService::where('user_id', $user->id)
            ->where('service_type', 'hotel');

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
        
        return view('hotel.index', compact(
            'submissions',
            'hotelService',
            'wallet',
            'role'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_field'   => 'required|exists:service_fields,id',
            'country'         => 'required|string|max:255',
            'hotel_name'      => 'required|string|max:255',
            'check_in'        => 'required|date|after_or_equal:today',
            'check_out'       => 'required|date|after_or_equal:check_in',
            'state'           => 'required|string|max:255',
            'lga'             => 'required|string|max:255',
            'address'         => 'required|string|max:1000',
            'email'           => 'required|email|max:255',
            'phone_number'    => 'required|string|max:20',
            'passport_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'           => 'nullable|string|max:1000',
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
            return redirect()->route('hotel.index')->withErrors(['wallet' => $msg])->withInput();
        }

        DB::beginTransaction();

        try {
            $passportUrl = null;
            if ($request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $fileName = 'hotel_id_' . Str::slug($user->email) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/hotel_ids', $fileName, 'public');
                $passportUrl = asset('storage/' . $path);
            }

            $wallet->decrement('balance', $totalAmount);

            $transactionRef = 'HTL' . date('ymdHis') . strtoupper(Str::random(4));
            $performedBy = trim("{$user->first_name} {$user->last_name}");

            $transaction = Transaction::create([
                'transaction_ref' => $transactionRef,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'description' => "Hotel Reservation: {$validated['hotel_name']} in {$validated['country']}",
                'type' => 'debit',
                'status' => 'completed',
                'performed_by' => $performedBy,
                'metadata' => [
                    'service' => $service->name,
                    'hotel_name' => $validated['hotel_name'],
                    'country' => $validated['country'],
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                    'state' => $validated['state'],
                    'lga' => $validated['lga'],
                    'address' => $validated['address'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'passport_url' => $passportUrl,
                ],
            ]);

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
                'country' => $validated['country'],
                'company_name' => $validated['hotel_name'],
                'departure_date' => $validated['check_in'],
                'return_date' => $validated['check_out'],
                'state' => $validated['state'],
                'lga' => $validated['lga'],
                'description' => "Address: " . $validated['address'] . "\nNotes: " . ($validated['notes'] ?? 'N/A'),
                'passport_url' => $passportUrl,
                'amount' => $totalAmount,
                'transaction_id' => $transaction->id,
                'submission_date' => now(),
                'status' => 'pending',
                'service_type' => 'hotel',
                'performed_by' => $performedBy,
            ]);

            DB::commit();

            return redirect()->route('hotel.index')->with([
                'status' => 'success',
                'message' => "An agent will contact you shortly for the reservation. Charged: NGN " . number_format($totalAmount, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hotel Reservation Error: ' . $e->getMessage());
            return redirect()->route('hotel.index')->withErrors([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ])->withInput();
        }
    }
}
