<?php

namespace App\Http\Controllers\P2P;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class P2PController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $wallet = Wallet::where('user_id', $userId)->first();
        
        return view('wallet.p2p', [
            'wallet' => $wallet,
            'title' => 'P2P Transfer'
        ]);
    }

    /**
     * Handle P2P fund transfer
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string', // Email or Phone
            'amount'    => 'required|numeric|min:100',
            'pin'       => 'required|string|digits:5',
            'description' => 'nullable|string|max:100'
        ]);

        $sender = Auth::user();
        $amount = $request->amount;
        $pin = $request->pin;
        $recipientIdentifier = $request->recipient;

        // 1. Verify PIN (Note: PIN is stored as plain text in ProfileController)
        if ($pin !== $sender->pin) {
            return back()->with('error', 'Invalid Transaction PIN.');
        }

        // 2. Find Recipient
        $recipient = User::where('email', $recipientIdentifier)
            ->orWhere('phone_no', $recipientIdentifier)
            ->first();

        if (!$recipient) {
            return back()->with('error', 'Recipient not found. Please check the email or phone number.');
        }

        if ($recipient->id === $sender->id) {
            return back()->with('error', 'You cannot send money to yourself.');
        }

        // 3. Process Transfer in Transaction
        try {
            DB::transaction(function () use ($sender, $recipient, $amount, $request) {
                $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
                $recipientWallet = Wallet::where('user_id', $recipient->id)->lockForUpdate()->first();

                if (!$senderWallet || $senderWallet->balance < $amount) {
                    throw new \Exception('Insufficient balance.');
                }

                // Debit Sender
                $senderWallet->decrement('balance', $amount);

                // Credit Recipient
                $recipientWallet->increment('balance', $amount);

                $transferRef = 'P2p-' . strtoupper(uniqid());
                $description = $request->description ?: "P2P transfer to " . $recipient->first_name;

                // Create Transaction for Sender
                Transaction::create([
                    'user_id'         => $sender->id,
                    'type'            => 'debit',
                    'amount'          => $amount,
                    'description'     => $description,
                    'status'          => 'completed',
                    'transaction_ref' => $transferRef,
                    'performed_by'    => $sender->first_name . ' ' . $sender->last_name,
                    'service_type'    => 'P2P Transfer'
                ]);

                // Create Transaction for Recipient
                Transaction::create([
                    'user_id'         => $recipient->id,
                    'type'            => 'credit',
                    'amount'          => $amount,
                    'description'     => "P2P transfer from " . $sender->first_name,
                    'status'          => 'completed',
                    'transaction_ref' => $transferRef,
                    'performed_by'    => $sender->first_name . ' ' . $sender->last_name,
                    'service_type'    => 'P2P Transfer'
                ]);
            });

            return redirect()->route('p2p.index')->with('success', '₦' . number_format($amount, 2) . ' successfully transferred to ' . $recipient->first_name . '.');

        } catch (\Exception $e) {
            Log::error('P2P Transfer Failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?: 'Transfer failed. Please try again.');
        }
    }

    /**
     * Verify recipient via AJAX
     */
    public function verifyRecipient(Request $request)
    {
        $identifier = $request->query('identifier');
        
        if (!$identifier) {
            return response()->json(['success' => false, 'message' => 'No identifier provided']);
        }

        $user = User::where('email', $identifier)
            ->orWhere('phone_no', $identifier)
            ->first();

        if ($user) {
            if ($user->id === Auth::id()) {
                return response()->json(['success' => false, 'message' => 'You cannot send money to yourself']);
            }

            $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
            return response()->json([
                'success' => true,
                'name' => $fullName
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User not found']);
    }
}
