<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\CableSubscription;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CableController extends Controller
{
    /**
     * Show Cable TV Purchase Page
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

        // Fetch Cable purchase history from new model
        $history = CableSubscription::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('utilities.buy-cable', compact('wallet', 'history'));
    }

    /**
     * Fetch Variations (Plans) from DB or VTPass
     */
    public function getVariations(Request $request)
    {
        $request->validate(['service_id' => 'required|string']);
        $serviceId = $request->service_id;

        // 1. Try fetching from Database first
        $variations = DB::table('data_variations')
            ->where('service_id', $serviceId)
            ->select('variation_code as code', 'name', 'variation_amount as amount')
            ->get();

        if ($variations->isNotEmpty()) {
            return response()->json(['success' => true, 'variations' => $variations]);
        }

        // 2. If not in DB, fetch from API
        try {
            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get(env('VARIATION_URL') . $serviceId);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['content']['variations'])) {
                    $variations = [];
                    foreach ($data['content']['variations'] as $v) {
                        // Prepare for response
                        $variations[] = [
                            'code'   => $v['variation_code'],
                            'name'   => $v['name'],
                            'amount' => $v['variation_amount'],
                        ];
                        
                        // Save to DB
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $v['variation_code'], 'service_id' => $serviceId],
                            [
                                'name'             => $v['name'],
                                'variation_amount' => $v['variation_amount'],
                                'fixed_price'      => $v['fixedPrice'] ?? 'Yes',
                                'updated_at'       => Carbon::now(),
                            ]
                        );
                    }
                    return response()->json(['success' => true, 'variations' => $variations]);
                }
            }
            return response()->json(['success' => false, 'message' => 'Failed to fetch plans.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching plans.']);
        }
    }

    /**
     * Verify Smartcard / IUC Number
     */
    public function verifyIuc(Request $request)
    {
        $request->validate([
            'service_id' => 'required|string',
            'billersCode' => 'required|string',
        ]);

        $cableIdMap = [
            'gotv'      => 1,
            'dstv'      => 2,
            'startimes' => 3,
            'showmax'   => 4,
        ];

        $cablename = $cableIdMap[strtolower($request->service_id)] ?? $request->service_id;

        try {
            $apiKey = env('ALRAHUZ_API_KEY');
            $url = env('ALRAHUZ_VALIDATE_IUC_URL');

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->get($url, [
                'smart_card_number' => $request->billersCode,
                'cablename'         => $cablename,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Alrahuz usually returns { "invalid": false, "name": "..." } or similar
                // Based on standard, I'll check for 'name' or successful status
                if (isset($data['name']) || (isset($data['invalid']) && $data['invalid'] == false)) {
                    return response()->json([
                        'success'        => true,
                        'customer_name'  => $data['name'] ?? $data['Customer_Name'] ?? 'Verified Customer',
                        'status'         => 'Active',
                        'due_date'       => 'N/A',
                        'customer_number'=> $request->billersCode,
                        'current_bouquet'=> 'N/A',
                        'renewal_amount' => 0,
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Unable to verify IUC number. ' . ($data['error'] ?? '')]);

        } catch (\Exception $e) {
            Log::error('Cable Verification Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed.']);
        }
    }

    /**
     * Purchase Cable Subscription
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'service_id'        => 'required|string',
            'billersCode'       => 'required|string',
            'subscription_type' => 'required|string|in:change,renew',
            'phone'             => 'required|numeric|digits:11',
            'amount'            => 'required|numeric',
            // variation_code is required if type is 'change'
            'variation_code'    => 'nullable|string',
        ]);

        $user = Auth::user();
        $requestId = RequestIdHelper::generateRequestId();
        $amount = $request->amount;

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        try {
            $payload = [
                'request_id'        => $requestId,
                'serviceID'         => $request->service_id,
                'billersCode'       => $request->billersCode,
                'subscription_type' => $request->subscription_type,
                'amount'            => $amount,
                'phone'             => $request->phone,
            ];

            if ($request->subscription_type === 'change') {
                if (!$request->variation_code) {
                    return back()->with('error', 'Please select a plan for bouquet change.');
                }
                $payload['variation_code'] = $request->variation_code;
            }

            $cableIdMap = [
                'gotv'      => 1,
                'dstv'      => 2,
                'startimes' => 3,
                'showmax'   => 4,
            ];

            $cablenameId = $cableIdMap[strtolower($request->service_id)] ?? $request->service_id;

            // Call Alrahuz API
            $apiKey = env('ALRAHUZ_API_KEY');
            $url = env('ALRAHUZ_CABLESUB_URL');

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'cablename'         => $cablenameId,
                'cableplan'         => $request->variation_code,
                'smart_card_number' => $request->billersCode,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['Status']) && strtolower($result['Status']) === 'successful' && !isset($result['error'])) {
                    $wallet->decrement('balance', $amount);

                    $serviceName = strtoupper($request->service_id);
                    $description = "{$serviceName} Subscription - IUC: {$request->billersCode}";

                    // Transaction Record
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $user->id,
                        'amount'          => $amount,
                        'description'     => $description,
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'        => json_encode([
                            'service_id'   => $request->service_id,
                            'billersCode'  => $request->billersCode,
                            'variation'    => $request->variation_code,
                            'api_response' => $result,
                        ]),
                        'performed_by' => $user->first_name . ' ' . $user->last_name,
                        'approved_by'  => $user->id,
                    ]);

                    // CableSubscription Record
                    CableSubscription::create([
                        'user_id'           => $user->id,
                        'transaction_ref'   => $requestId,
                        'cablename'         => $serviceName,
                        'cableplan'         => $request->variation_code,
                        'smart_card_number' => $request->billersCode,
                        'amount'            => $amount,
                        'status'            => 'completed',
                    ]);

                    // NOTE: Report::create REMOVED as requested

                    return redirect()->route('thankyou')->with([
                        'success' => 'Cable subscription successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->billersCode,
                        'amount'  => $amount,
                        'token'   => 'Subscription Active',
                        'network' => $serviceName
                    ]);

                } else {
                    Log::error('Alrahuz Cable API Error', ['response' => $result]);

                    // Create failed transaction
                    $serviceName = strtoupper($request->service_id);
                    $description = "{$serviceName} Subscription - IUC: {$request->billersCode}";

                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $user->id,
                        'amount'          => $amount,
                        'description'     => "Failed " . $description,
                        'type'            => 'debit',
                        'status'          => 'failed',
                        'service_type'    => 'cable',
                        'performed_by'    => $user->first_name . ' ' . $user->last_name,
                        'approved_by'     => $user->id,
                        'metadata'        => json_encode([
                            'service_id'   => $request->service_id,
                            'billersCode'  => $request->billersCode,
                            'variation'    => $request->variation_code,
                            'api_response' => $result
                        ])
                    ]);

                    $errorMessage = $result['msg'] ?? $result['message'] ?? 'Subscription failed. Please try again.';
                    return back()->with('error', $errorMessage);
                }
            } else {
                Log::error('Alrahuz Cable HTTP Error', ['body' => $response->body()]);
                return back()->with('error', 'Service unavailable.');
            }

        } catch (\Exception $e) {
            Log::error('Cable Exception: ' . $e->getMessage());
            return back()->with('error', 'An error occurred.');
        }
    }
}
