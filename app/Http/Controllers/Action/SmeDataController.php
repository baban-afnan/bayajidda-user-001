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

class SmeDataController extends Controller
{
    use ActiveUsers;

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
        $network = trim($request->id);
        $types = SmeData::where('network', 'LIKE', $network)
            ->where('status', 'enabled')
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
        $network = trim($request->id);
        $type = trim($request->type);
        $user = Auth::user();
        $role = $user->user_type ?? 'user';

        $plans = SmeData::where('network', 'LIKE', $network)
            ->where('plan_type', 'LIKE', $type)
            ->where('status', 'enabled')
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
        
        $plan = SmeData::where('data_id', $planId)->first();
        if (!$plan) {
            return back()->with('error', 'Invalid data plan selected.');
        }

        $service = Service::firstOrCreate(
            ['name' => 'SME Data'],
            ['is_active' => 1]
        );

        if (!$service->is_active) {
            return back()->with('error', 'SME Data service is currently unavailable.');
        }

        $payableAmount = $plan->calculatePriceForRole($user->user_type ?? 'user');
        $description = "{$plan->size} {$plan->plan_type} for {$mobile} ({$plan->network})";
        $requestId = RequestIdHelper::generateRequestId();
        $performedBy = $user->first_name . ' ' . $user->last_name;

        DB::beginTransaction();

        try {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            
            if (!$wallet || $wallet->status !== 'active') {
                DB::rollBack();
                return back()->with('error', 'Your wallet is not active or found.');
            }

            if ($wallet->balance < $payableAmount) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
            }

            // API Call to Alrahuz
            $apiKey = env('ALRAHUZ_API_KEY');
            
            $networkMapping = [
                'MTN' => 1,
                'GLO' => 2,
                '9MOBILE' => 3,
                'AIRTEL' => 4,
            ];
            
            $networkId = $networkMapping[strtoupper($plan->network)] ?? 1;

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post(env('ALRAHUZ_BASE_URL'), [
                'network'       => $networkId,
                'mobile_number' => $mobile,
                'plan'          => $planId,
                'Ported_number' => true,
            ]);

            $data = $response->json();
            Log::info('Alrahuz Data API Response', ['response' => $data]);

            // Charge only if transaction is successful
            $isSuccess = ($response->status() === 200 && isset($data['Status']) && strtolower($data['Status']) === 'successful' && !isset($data['error']));

            if ($isSuccess) {
                // Success: Debit Wallet and Create Transaction
                $wallet->decrement('balance', $payableAmount);

                $transaction = Transaction::create([
                    'transaction_ref' => $requestId,
                    'user_id'         => $user->id,
                    'amount'          => $payableAmount,
                    'description'     => "SME Data purchase: " . $description,
                    'type'            => 'debit',
                    'status'          => 'completed',
                    'service_type'    => 'sme_data',
                    'performed_by'    => $performedBy,
                    'approved_by'     => $user->id,
                    'metadata'        => json_encode([
                        'phone'   => $mobile,
                        'network' => $plan->network,
                        'plan_id' => $planId,
                    ])
                ]);
                
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
            } else {
                // Failure: Rollback DB lock but save a failed transaction
                DB::rollBack();

                Transaction::create([
                    'transaction_ref' => $requestId,
                    'user_id'         => $user->id,
                    'amount'          => $payableAmount,
                    'description'     => "Failed SME Data purchase: " . $description,
                    'type'            => 'debit',
                    'status'          => 'failed',
                    'service_type'    => 'sme_data',
                    'performed_by'    => $performedBy,
                    'approved_by'     => $user->id,
                    'metadata'        => json_encode([
                        'phone'   => $mobile,
                        'network' => $plan->network,
                        'plan_id' => $planId,
                        'api_response' => $data
                    ])
                ]);

                $errorMessage = $data['message'] ?? $data['msg'] ?? 'Data purchase failed. Please try again.';
                return redirect()->back()->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SME Data Purchase Error: ' . $e->getMessage());

            Transaction::create([
                'transaction_ref' => $requestId,
                'user_id'         => $user->id,
                'amount'          => $payableAmount,
                'description'     => "Failed SME Data purchase (Error): " . $description,
                'type'            => 'debit',
                'status'          => 'failed',
                'service_type'    => 'sme_data',
                'performed_by'    => $performedBy,
                'approved_by'     => $user->id,
                'metadata'        => json_encode([
                    'phone' => $mobile,
                    'error' => $e->getMessage()
                ])
            ]);

            return redirect()->back()->with('error', 'An error occurred while processing your request. Please try again later.');
        }
    }
}

