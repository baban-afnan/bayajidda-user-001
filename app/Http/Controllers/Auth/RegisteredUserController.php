<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeBonusMail;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\BonusHistory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show registration page
     */
    public function create($referral = null): View
    {
        return view('auth.register', ['referral' => $referral]);
    }

    /**
     * Handle registration
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:50'],
            'terms'   => ['accepted'],
        ]);

        DB::beginTransaction();

        try {
            // Get referral details (if any)
            $referralDetails = $this->getBonus($request);
            if (isset($referralDetails['error'])) {
                return back()->withInput()->with('error', $referralDetails['error']);
            }

            // Create user
            $user = User::create([
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'referral_code' => $referralDetails['myOwnCode'],
            ]);

            // Fetch welcome bonus from Service Field
            $serviceField = ServiceField::where('field_name', 'new user registration')
                ->whereHas('service', function ($q) {
                    $q->where('name', 'NEW USER');
                })
                ->first();

            $bonusAmount = $serviceField ? (float) $serviceField->base_price : 200.00;

            // Create wallet with welcome bonus
            Wallet::create([
                'user_id'           => $user->id,
                'balance'           => $bonusAmount,
                'hold_amount'       => 0.00,
                'available_balance' => 0.00,
                'wallet_number'     => (string) random_int(1000000000, 9999999999),
                'currency'          => 'NGN',
                'status'            => 'active',
                'last_activity'     => now(),
                'bonus'             => 0.00,
            ]);

            // Create welcome bonus transaction record
            Transaction::create([
                'user_id'         => $user->id,
                'type'            => 'credit',
                'amount'          => $bonusAmount,
                'description'     => 'Welcome Bonus for new user registration',
                'status'          => 'completed',
                'transaction_ref' => 'WB-' . strtoupper(Str::random(10)),
                'performed_by'    => 'System',
                'service_type'    => 'welcome_bonus',
            ]);

            // Add referral bonus if applicable
            if ($referralDetails['referral_id']) {
                $this->addBonus($referralDetails['referral_id'], $referralDetails['referral_bonus'], $user);
            }

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            // Send Welcome Mail
            try {
                Mail::to($user->email)->queue(new WelcomeBonusMail($user, $bonusAmount));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email to ' . $user->email . ': ' . $e->getMessage());
            }

            return redirect()->route('dashboard')->with('success', 'Account created successfully. A welcome bonus of ₦' . number_format($bonusAmount, 2) . ' has been credited to your wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Process referral bonus info
     */
    private function getBonus(Request $request): array
    {
        $referral_id = null;
        $referral_bonus = 0.00;

        if ($request->filled('referral_code')) {
            $referralUser = User::where('referral_code', $request->referral_code)->first();

            if ($referralUser) {
                $referral_id = $referralUser->id;
                $referral_bonus = $referralUser->referral_bonus > 0
                    ? $referralUser->referral_bonus
                    : (DB::table('referral_bonus')->value('bonus') ?? 0.00);
            } else {
                return ['error' => 'Invalid referral code.'];
            }
        }

        // Generate a unique referral code for the new user
        do {
            $myOwnCode = substr(md5(uniqid($request->email, true)), 0, 6);
        } while (User::where('referral_code', $myOwnCode)->exists());

        return [
            'referral_id'    => $referral_id,
            'referral_bonus' => $referral_bonus,
            'myOwnCode'      => $myOwnCode,
        ];
    }

    /**
     * Credit bonus to referrer
     */
    private function addBonus(int $referral_id, float $referral_bonus, User $referredUser): void
    {
        $wallet = Wallet::where('user_id', $referral_id)->first();

        if ($wallet) {
            $wallet->balance = ($wallet->balance ?? 0) + $referral_bonus;
            $wallet->save();

            BonusHistory::create([
                'user_id'          => $referral_id,
                'referred_user_id' => $referredUser->id,
                'amount'           => $referral_bonus,
                'type'             => 'referral',
            ]);

            Transaction::create([
                'user_id'         => $referral_id,
                'type'            => 'credit',
                'amount'          => $referral_bonus,
                'description'     => 'Referral Bonus for inviting ' . ($referredUser->first_name ?? 'a new user'),
                'status'          => 'completed',
                'transaction_ref' => 'REF-' . strtoupper(Str::random(10)),
                'performed_by'    => 'System',
                'service_type'    => 'referral_bonus',
            ]);
        }
    }
}
