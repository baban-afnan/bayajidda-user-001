<?php

namespace App\Http\Controllers\Action;

use App\Helpers\RequestIdHelper;
use App\Http\Controllers\Controller;
use App\Models\EducationalPin;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\ActiveUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EducationalController extends Controller
{
    use ActiveUsers;

    protected $loginUserId;

    public function __construct()
    {
        $this->loginUserId = Auth::id();
    }

    /**
     * Show Educational Pin Services & Price Lists
     */
    public function pin(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Load pin variations
        $pins = DB::table('data_variations')->whereIn('service_id', ['waec', 'waec-registration'])->get();

        // Load history from EducationalPin model
        $history = \App\Models\EducationalPin::where('user_id', $user->id)->latest()->paginate(10);

        return view('utilities.buy-educational-pin', compact('user', 'wallet', 'pins', 'history'));
    }

    /**
     * Verify Transaction PIN
     */
    public function verifyPin(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'Unauthorized']);
        }

        $isValid = ($request->pin == $user->pin);
        return response()->json(['valid' => $isValid]);
    }

    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    /**
     * Fetch variations dynamically from VTpass and store in DB
     */
    public function getVariation(Request $request)
    {
        try {
            // Determine serviceID based on type
            $type = $request->type;
            $url = env('VARIATION_URL') . $type;

            // Special handling for JAMB if needed, but usually VTPass uses 'jamb' as serviceID for variations too
            // If type is 'jamb', URL is .../service-variations?serviceID=jamb

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['content']['variations'])) {
                    $serviceName = $data['content']['ServiceName'];
                    $serviceId = $data['content']['serviceID'];
                    $convenienceFee = $data['content']['convinience_fee'] ?? '0%';

                    foreach ($data['content']['variations'] as $variation) {
                        DB::table('data_variations')->updateOrInsert(
                            ['variation_code' => $variation['variation_code']],
                            [
                                'service_name'     => $serviceName,
                                'service_id'       => $serviceId,
                                'convenience_fee'  => $convenienceFee,
                                'name'             => $variation['name'],
                                'variation_amount' => $variation['variation_amount'],
                                'fixed_price'      => $variation['fixedPrice'],
                                'created_at'       => Carbon::now(),
                                'updated_at'       => Carbon::now(),
                            ]
                        );
                    }

                    return response()->json(['success' => true, 'message' => 'Variation list updated successfully.']);
                }
            }

            Log::error('VTpass Variation Fetch Failed', ['response' => $response->json()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch variations.']);
        } catch (\Exception $e) {
            Log::error('VTpass Variation Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }

    /**
     * Buy Educational Pin (WAEC / WAEC Registration)
     */
    public function buypin(Request $request)
    {
        $request->validate([
            'service'  => ['required', 'string', 'in:waec,neco'],
            'quantity' => ['required', 'integer', 'min:1', 'max:5'],
            'mobileno' => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $requestId = RequestIdHelper::generateRequestId();

        try {
            // Get the selected variation details
            $variation = DB::table('data_variations')->where('variation_code', $request->type)->first();

            if (!$variation) {
                return back()->with('error', 'Invalid educational pin type selected.');
            }

            $fee = $variation->variation_amount;
            $description = $variation->name ?? 'Educational Pin';

            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            if (!$wallet || $wallet->balance < $fee) {
                return back()->with('error', 'Insufficient wallet balance for this transaction.');
            }

            // Call Alrahuz API
            $apiKey = env('ALRAHUZ_API_KEY');
            $url = env('ALRAHUZ_EPIN_URL');

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($url, [
                'exam_name' => strtoupper($request->service),
                'quantity'  => (int)$request->quantity,
            ]);

            $result = $response->json();
            Log::info('Alrahuz Educational Pin API Response', ['response' => $result]);

            if ($response->successful() && isset($result['Status']) && strtolower($result['Status']) === 'successful' && !isset($result['error'])) {
                // Deduct wallet balance
                $wallet->decrement('balance', $fee);

                // Extract PINs - Alrahuz usually returns them in a field, I'll store the whole response or specific field
                // Based on standard VTU APIs, it might be 'pin' or 'pins' or in 'data'
                $finalPinData = $result['pin'] ?? $result['pins'] ?? $result['data'] ?? 'Check History';

                $payer_name = $user->first_name . ' ' . $user->last_name;
                
                // Save to EducationalPin model
                \App\Models\EducationalPin::create([
                    'user_id'         => $user->id,
                    'transaction_ref' => $requestId,
                    'exam_name'       => strtoupper($request->service),
                    'quantity'        => $request->quantity,
                    'pins'            => is_array($finalPinData) ? json_encode($finalPinData) : $finalPinData,
                    'amount'          => $fee,
                    'status'          => 'completed'
                ]);

                // Create Transaction record for user history
                Transaction::create([
                    'transaction_ref' => $requestId,
                    'user_id'         => $user->id,
                    'amount'          => $fee,
                    'description'     => "Educational pin purchase: " . strtoupper($request->service) . " (x{$request->quantity})",
                    'type'            => 'debit',
                    'status'          => 'completed',
                    'metadata'        => json_encode([
                        'phone'    => $request->mobileno,
                        'service'  => $request->service,
                        'quantity' => $request->quantity,
                        'pins'     => $finalPinData,
                        'api_response' => $result,
                    ]),
                    'performed_by' => $payer_name,
                    'approved_by'  => $user->id,
                ]);

                // NOTE: Report::create is REMOVED as requested.

                return redirect()->route('thankyou2')->with([
                    'success' => 'Educational pin purchase successful!',
                    'ref'     => $requestId,
                    'mobile'  => $request->mobileno,
                    'amount'  => $fee,
                    'pins'    => $finalPinData,
                    'network' => strtoupper($request->service)
                ]);
            } else {
                // Create failed transaction
                $payer_name = $user->first_name . ' ' . $user->last_name;
                Transaction::create([
                    'transaction_ref' => $requestId,
                    'user_id'         => $user->id,
                    'amount'          => $fee,
                    'description'     => "Failed Educational pin purchase: " . strtoupper($request->service) . " (x{$request->quantity})",
                    'type'            => 'debit',
                    'status'          => 'failed',
                    'service_type'    => 'educational_pin',
                    'performed_by'    => $payer_name,
                    'approved_by'     => $user->id,
                    'metadata'        => json_encode([
                        'phone'    => $request->mobileno,
                        'service'  => $request->service,
                        'quantity' => $request->quantity,
                        'api_response' => $result,
                    ])
                ]);

                return back()->with('error', 'Purchase failed. ' . ($result['msg'] ?? $result['message'] ?? 'Please try again later.'));
            }
        } catch (\Exception $e) {
            Log::error('Educational Pin Purchase Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show JAMB Purchase Page
     */
    public function jamb(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'status' => 'active']
        );

        // Fetch JAMB purchase history
        $history = \App\Models\Report::where('user_id', $user->id)
            ->where('type', 'jamb')
            ->latest()
            ->paginate(10);

        // Fetch JAMB variations
        $variations = DB::table('data_variations')->where('service_id', 'jamb')->get();

        return view('utilities.buy-jamb', compact('wallet', 'history', 'variations'));
    }

    /**
     * Verify JAMB Profile ID
     */
    public function verifyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
        ]);

        try {
            // Map the selected service to the VTPass Service ID
            // Usually 'jamb' is the serviceID for both UTME and DE
            $vtpassServiceId = 'jamb'; 

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('BASE_URL', 'https://sandbox.vtpass.com/api') . '/merchant-verify', [
                'serviceID'   => $vtpassServiceId,
                'billersCode' => $request->profile_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['code']) && $data['code'] == '000') {
                    $customerName = $data['content']['Customer_Name'] ?? 'Unknown';
                    
                    // Fetch price from DB
                    // The frontend sends 'jamb' or 'jamb-de' as the 'service' (which acts as variation code here)
                    // We look up the 'data_variations' table using this code.
                    // If not found, we might default to a known price or error.
                    
                    $variationCode = $request->service; // 'jamb' or 'jamb-de'
                    
                    // Try to find by variation_code directly
                    $variation = DB::table('data_variations')->where('variation_code', $variationCode)->first();
                    
                    // If not found, try to find by service_id 'jamb' and name like... (fallback)
                    if (!$variation) {
                         // Fallback: if user sent 'jamb', look for 'utme' maybe? 
                         // For now, let's assume the DB is seeded with 'jamb' and 'jamb-de' as variation_codes.
                         // If not, we return 0 and user can't buy.
                    }

                    $amount = $variation ? $variation->variation_amount : 0;

                    return response()->json([
                        'success' => true, 
                        'customer_name' => $customerName,
                        'amount' => $amount
                    ]);
                }
            }
            
            return response()->json(['success' => false, 'message' => 'Invalid Profile ID or Service unavailable.']);

        } catch (\Exception $e) {
            Log::error('JAMB Verification Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed.']);
        }
    }

    /**
     * Buy JAMB PIN
     */
    public function buyJamb(Request $request)
    {
        $request->validate([
            'service'    => 'required|string',
            'profile_id' => 'required|string',
            'mobileno'   => 'required|numeric|digits:11',
        ]);

        $user = Auth::user();
        $requestId = RequestIdHelper::generateRequestId();

        try {
            // Get Price
            $variation = DB::table('data_variations')->where('variation_code', $request->service)->first();
            if (!$variation) {
                return back()->with('error', 'Invalid JAMB service selected.');
            }

            $fee = $variation->variation_amount;
            $description = $variation->name ?? 'JAMB PIN';

            $wallet = Wallet::where('user_id', $this->loginUserId)->first();
            if (!$wallet || $wallet->balance < $fee) {
                return back()->with('error', 'Insufficient wallet balance.');
            }

            // Call API
            // Note: For JAMB, billersCode is the Profile ID.
            // ServiceID is 'jamb' for both UTME and DE.
            // Variation code distinguishes them (e.g. 'jamb' or 'utme', 'jamb-de' or 'de').
            
            $apiServiceId = 'jamb'; // Always 'jamb' for VTPass JAMB services

            $response = Http::withHeaders([
                'api-key'    => env('API_KEY'),
                'secret-key' => env('SECRET_KEY'),
            ])->post(env('MAKE_PAYMENT'), [
                'request_id'     => $requestId,
                'serviceID'      => $apiServiceId,
                'billersCode'    => $request->profile_id,
                'variation_code' => $request->service, // variation_code
                'phone'          => $request->mobileno,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                $successCodes = ['0', '00', '000', '200'];
                $isSuccessful = (isset($result['code']) && in_array((string)$result['code'], $successCodes)) ||
                                (isset($result['status']) && strtolower($result['status']) === 'success');

                if ($isSuccessful) {
                    $wallet->decrement('balance', $fee);

                    // Extract PIN
                    $purchasedCode = $result['purchased_code'] ?? null;
                    if (!$purchasedCode && isset($result['cards'][0]['Pin'])) {
                        $purchasedCode = $result['cards'][0]['Pin'];
                    }
                    $finalToken = $purchasedCode ?? 'Check History';

                    $payer_name = $user->first_name . ' ' . $user->last_name;
                    $transDescription = "{$description} Purchase - Profile: {$request->profile_id} - PIN: {$finalToken}";

                    // Transaction
                    Transaction::create([
                        'transaction_ref' => $requestId,
                        'user_id'         => $this->loginUserId,
                        'amount'          => $fee,
                        'description'     => $transDescription,
                        'type'            => 'debit',
                        'status'          => 'completed',
                        'metadata'        => json_encode([
                            'profile_id'     => $request->profile_id,
                            'purchased_code' => $finalToken,
                            'api_response'   => $result,
                        ]),
                        'performed_by' => $payer_name,
                        'approved_by'  => $this->loginUserId,
                    ]);

                    // Report
                    \App\Models\Report::create([
                        'user_id'      => $user->id,
                        'phone_number' => $request->profile_id, // Saving Profile ID in phone_number col or description? Let's use phone_number for quick ref or the actual phone.
                        // Actually, let's save the phone number entered.
                        'phone_number' => $request->mobileno,
                        'network'      => $request->service, // jamb or jamb-de
                        'ref'          => $requestId,
                        'amount'       => $fee,
                        'status'       => 'successful',
                        'type'         => 'jamb',
                        'description'  => $transDescription,
                        'old_balance'  => $wallet->balance + $fee,
                        'new_balance'  => $wallet->balance,
                    ]);

                    return redirect()->route('thankyou')->with([
                        'success' => 'JAMB PIN purchase successful!',
                        'ref'     => $requestId,
                        'mobile'  => $request->mobileno,
                        'amount'  => $fee,
                        'token'   => $finalToken,
                        'network' => strtoupper($description)
                    ]);
                } else {
                    Log::error('JAMB API Error', ['response' => $result]);
                    return back()->with('error', 'Purchase failed. ' . ($result['response_description'] ?? 'Try again.'));
                }
            } else {
                Log::error('JAMB HTTP Error', ['body' => $response->body()]);
                return back()->with('error', 'Service unavailable.');
            }

        } catch (\Exception $e) {
            Log::error('JAMB Exception: ' . $e->getMessage());
            return back()->with('error', 'An error occurred.');
        }
    }
}


