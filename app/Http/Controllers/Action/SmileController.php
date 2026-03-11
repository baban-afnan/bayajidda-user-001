<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\SmilePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmileController extends Controller
{
    protected $loginUserId;
    
    // API Configuration - loaded from .env
    private function getApiBaseUrl()
    {
        return env('JIDDA_BASE_URL', 'https://jiddadata.com/');
    }

    private function getApiKey()
    {
        return env('JIDDA_API_KEY');
    }

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Smile purchase form
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $plans = SmilePlan::orderBy('price', 'asc')->get();

        return view('utilities.smile', [
            'user'   => $user,
            'wallet' => $wallet,
            'plans'  => $plans
        ]);
    }

    /**
     * Handle Smile Purchase
     */
    public function buy(Request $request)
    {
        // 1 & 2. Authenticate user & Validate request
        $request->validate([
            'actype'     => 'required|string|in:AccountNumber,PhoneNumber',
            'mobileno'   => 'required|string', 
            'plan_id'    => 'required|exists:smile_plans,plan_id',
        ]);

        $user   = Auth::user();
        $accountType = $request->actype;
        $mobile  = $request->mobileno;
        $planId  = $request->plan_id;
        $requestId = RequestIdHelper::generateRequestId();

        // 3. Check service active
        $service = Service::where('name', 'Smile')->first();
        if ($service && !$service->is_active) {
            return redirect()->back()->with('error', 'Smile service is currently inactive. Please try again later.');
        }

        // 4. Calculate price
        $plan = SmilePlan::where('plan_id', $planId)->first();
        if (!$plan) {
            return redirect()->back()->with('error', 'Invalid plan selected.');
        }

        $amount = $plan->price;
        $payableAmount = $amount; 

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 5. Lock wallet row/Check balance
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet || $wallet->balance < $payableAmount) {
                \Illuminate\Support\Facades\DB::rollBack();
                return redirect()->back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
            }

            // 6. Create transaction (pending)
            $transaction = Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $payableAmount,
                'description'     => "Smile purchase of {$plan->name} to {$mobile} ({$accountType})",
                'type'            => 'debit',
                'status'          => 'pending',
                'metadata'        => json_encode([
                    'phone'             => $mobile,
                    'account_type'      => $accountType,
                    'plan_id'           => $planId,
                    'original_amt'      => $amount,
                ]),
                'performed_by' => $user->first_name . ' ' . $user->last_name,
                'approved_by'  => $user->id,
            ]);

            // 7. Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->getApiKey(), 
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($this->getApiBaseUrl() . 'api/smile', [
                'mobile_number' => $mobile,
                'plan'          => $planId,
                'actype'        => $accountType,
            ]);

            $data = $response->json();
            Log::info('Jidda Data Smile API Response', ['response' => $data]);

            $isSuccessful = false;
            
            // Check success logic using response actual format
            // e.g. {"Status":"successful", "ident":"..."}
            if ($response->successful() && isset($data['Status']) && strtolower($data['Status']) === 'successful') {
                $isSuccessful = true;
            }

            if ($isSuccessful) {
                $transactionRef = $data['ident'] ?? $requestId;

                // 8. Update transaction status
                $transaction->update([
                    'status' => 'completed',
                    'transaction_ref' => $transactionRef,
                    'metadata' => json_encode(array_merge(json_decode($transaction->metadata, true), ['api_response' => $data]))
                ]);

                // 9. Debit wallet
                $wallet->decrement('balance', $payableAmount);

                \Illuminate\Support\Facades\DB::commit();

                // 10. Redirect to thankyou page
                return redirect()->route('thankyou')->with([
                    'success'           => 'Smile Data purchase successful!',
                    'transaction_ref'   => $transactionRef,
                    'request_id'        => $requestId,
                    'mobile'            => $mobile,
                    'network'           => 'Smile',
                    'amount'            => $amount,
                    'paid'              => $payableAmount,
                    'commission_earned' => 0,
                    'type'              => 'smile'
                ]);
            }

            // Mark as failed
            $transaction->update([
                'status' => 'failed',
                'metadata' => json_encode(array_merge(json_decode($transaction->metadata, true), ['api_response' => $data]))
            ]);
            \Illuminate\Support\Facades\DB::commit();

            Log::error('Jidda Data Smile API Response Error', ['response' => $data]);
            
            $errorMessage = $data['message'] ?? ($data['api_response'] ?? 'Smile purchase failed. Please try again.');
            return redirect()->back()->with('error', $errorMessage);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Smile Purchase Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your request. Please try again.');
        }
    }
}
