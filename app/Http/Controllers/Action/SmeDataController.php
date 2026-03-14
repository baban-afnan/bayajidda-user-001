<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\SmeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Service;
use Illuminate\Support\Str;

class SmeDataController extends Controller
{
    use ActiveUsers;

    // API Configuration
    private function getApiBaseUrl()
    {
        return env('GSUBZ_BASE_URL');
    }

    private function getApiToken()
    {
        return env('GSUBZ_API_KEY');
    }

    /**
     * Show SME Data Purchase Page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        $networks = SmeData::select('network')->distinct()->get();

        // Price lists for the advert section
        $priceList1 = DB::table('data_variations')->where('service_id', 'mtn-data')->paginate(10, ['*'], 'table1_page');
        $priceList2 = DB::table('data_variations')->where('service_id', 'airtel-data')->paginate(10, ['*'], 'table2_page');
        $priceList3 = DB::table('data_variations')->where('service_id', 'glo-data')->paginate(10, ['*'], 'table3_page');
        $priceList4 = DB::table('data_variations')->where('service_id', 'etisalat-data')->paginate(10, ['*'], 'table4_page');
        $priceList5 = DB::table('data_variations')->where('service_id', 'smile-direct')->paginate(10, ['*'], 'table5_page');
        $priceList6 = DB::table('data_variations')->where('service_id', 'spectranet')->paginate(10, ['*'], 'table6_page');

        return view('utilities.buy-sme-data', compact(
            'user', 
            'wallet', 
            'networks',
            'priceList1',
            'priceList2',
            'priceList3',
            'priceList4',
            'priceList5',
            'priceList6'
        ));
    }

    /**
     * Fetch Data Types for a Network
     */
    public function fetchDataType(Request $request)
    {
        $network = $request->id;
        $types = SmeData::where('network', $network)
            ->select('plan_type')
            ->distinct()
            ->get();
        return response()->json($types);
    }

    /**
     * Fetch Data Plans for a Network and Type
     */
    public function fetchDataPlan(Request $request)
    {
        $network = $request->id;
        $type = $request->type;
        $user = Auth::user();
        $role = $user->user_type ?? 'user';

        $plans = SmeData::where('network', $network)
            ->where('plan_type', $type)
            ->where('status', 1)
            ->get();
            
        foreach ($plans as $plan) {
            $finalPrice = $plan->calculatePriceForRole($role);
            $plan->formatted_text = "{$plan->size} {$plan->plan_type} (₦" . number_format((float)$finalPrice, 2) . ") {$plan->validity}";
        }

        return response()->json($plans);
    }

    /**
     * Fetch Plan Price
     */
    public function fetchSmeBundlePrice(Request $request)
    {
        $planId = $request->id;
        $plan = SmeData::where('data_id', $planId)->first();
        
        if (!$plan) {
            return response()->json("0.00");
        }

        $user = Auth::user();
        $finalPrice = $plan->calculatePriceForRole($user->user_type ?? 'user');

        return response()->json(number_format((float)$finalPrice, 2));
    }

    /**
     * Buy SME Data Bundle
     */
    public function buySMEdata(Request $request)
    {
        $request->validate([
            'network'  => 'required|string',
            'type'     => 'required|string',
            'plan'     => 'required|string',
            'mobileno' => 'required|numeric|digits:11'
        ]);

        $user = Auth::user();
        $mobile = $request->mobileno;
        $planId = $request->plan;
        
        // 1. Validate request and find plan
        $plan = SmeData::where('data_id', $planId)->first();
        if (!$plan) {
            return back()->with('error', 'Invalid data plan selected.');
        }

        // 2. Ensure Service Exists and is Active
        $service = Service::firstOrCreate(
            ['name' => 'SME Data'],
            ['is_active' => 1]
        );

        if (!$service->is_active) {
            return back()->with('error', 'SME Data service is currently unavailable.');
        }

        // 3. Calculate Price
        $payableAmount = $plan->calculatePriceForRole($user->user_type ?? 'user');
        $description = "{$plan->size} {$plan->plan_type} for {$mobile} ({$plan->network})";
        $requestId = RequestIdHelper::generateRequestId();
        $performedBy = $user->first_name . ' ' . $user->last_name;

        DB::beginTransaction();

        try {
            // 4. Lock Wallet Row & Check Active
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            
            if (!$wallet) {
                return back()->with('error', 'Wallet not found.');
            }

            if ($wallet->status !== 'active') {
                return back()->with('error', 'Your wallet is not active.');
            }

            // 5. Check Balance
            if ($wallet->balance < $payableAmount) {
                return redirect()->back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
            }

            // 6. Create Transaction (Pending/Processing)
            $transaction = Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $payableAmount,
                'description'     => "SME Data purchase: " . $description,
                'type'            => 'debit',
                'status'          => 'processing',
                'service_type'    => 'sme_data',
                'performed_by'    => $performedBy,
                'approved_by'     => $user->id,
                'metadata'        => [
                    'phone'   => $mobile,
                    'network' => $plan->network,
                    'plan_id' => $planId,
                ]
            ]);

            // 7. Debit Wallet
            $wallet->decrement('balance', $payableAmount);

        
            // 8. API Call to GSUBZ
            $apiKey = $this->getApiToken();
            $serviceID = strtolower($plan->network) . '_sme'; // e.g., mtn_sme

            $response = Http::asForm()->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
            ])->post($this->getApiBaseUrl(), [
                'serviceID'  => $serviceID,
                'plan'       => $planId,
                'api'        => $apiKey,
                'amount'     => '',
                'phone'      => $mobile,
                'requestID'  => $requestId,
            ]);

            $data = $response->json();
            Log::info('SME Data API Response', ['response' => $data]);

            $apiCode = $data['code'] ?? null;
            $apiStatus = $data['status'] ?? null;
            
            // Check success and failed status
            $isSuccess = ($response->status() == 200 && $apiCode == 200 && $apiStatus == "TRANSACTION_SUCCESSFUL");
            $isFailed = (strtoupper($apiStatus) == 'FAILED' || $apiCode == 400); // 400 often means validation/insufficient vendor balance

            if ($isSuccess) {
                // Success: Update and Commit
                $transaction->update(['status' => 'completed']);
                
                DB::commit();

                return redirect()->route('thankyou')->with([
                    'success'         => 'Data purchase successful!',
                    'transaction_ref' => $requestId,
                    'request_id'      => $requestId,
                    'mobile'          => $mobile,
                    'network'         => $plan->network,
                    'amount'          => $payableAmount,
                    'paid'            => $payableAmount,
                    'type'            => 'SME Data'
                ]);
            } elseif ($isFailed) {
                // Confirmed Failure: Rollback
                DB::rollBack();
                $errorMessage = $data['message'] ?? 'Data purchase failed. Please try again.';
                return redirect()->back()->with('error', $errorMessage);
            } else {
                // Else (Processing/Unknown): Keep debit and Commit
                // The wallet is already debited and records are in 'processing' state.
                DB::commit();
                
                return redirect()->route('thankyou')->with([
                    'success'         => 'Transaction is being processed. Please check your history in a few minutes.',
                    'transaction_ref' => $requestId,
                    'request_id'      => $requestId,
                    'mobile'          => $mobile,
                    'network'         => $plan->network,
                    'amount'          => $payableAmount,
                    'paid'            => $payableAmount,
                    'type'            => 'SME Data'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SME Data Purchase Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your request. Please try again later.');
        }
    }
}
