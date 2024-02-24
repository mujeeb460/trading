<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deposit;
use Illuminate\Support\Facades\Log; // Import the Log class
use App\Models\Balance;
use App\Models\Withdrawal;

class DepositController extends Controller
{
    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'deposit_amount' => 'required|numeric',
            'deposit_currency' => 'required|in:HTG,USD',
            'payment_method' => 'required|in:cashapp,paypal,zelle,4poch,moncash,natcash',
            'deposit_type' => 'required|in:automatic,manual',
            'transaction_id' => $request->deposit_type == 'manual' ? 'required|string' : '',
            'proof_of_payment' => $request->deposit_type == 'manual' ? 'required|image|max:2048' : 'nullable',
        ]);

        // Create a new Deposit instance
        $deposit = new Deposit();
        $deposit->user_id = auth()->id();
        $deposit->amount = $request->deposit_amount;
        $deposit->currency = $request->deposit_currency;
        $deposit->payment_method = $request->payment_method;
        $deposit->deposit_type = $request->deposit_type;
        $deposit->transaction_id = $request->transaction_id;

        // Save the proof of payment file only if the type is manual
        if ($request->deposit_type == 'manual' && $request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('payment_proof'), $filename);
            $deposit->proof_of_payment = $filename;
        }

        // Save the deposit record with pending status
        $deposit->status = 'pending';
        $deposit->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Deposit has been recorded successfully.');
    }

    

    public function showProofOfPayment($filename)
    {
        $path = public_path('payment_proof/' . $filename);

        if (file_exists($path)) {
            return response()->file($path);
        } else {
            abort(404);
        }
    }

    // public function userDeposits()
    // {
    //     $user_id = auth()->user()->id;
        
    //     // Récupérer les dépôts de l'utilisateur
    //     $userDeposits = Deposit::where('user_id', $user_id)->latest()->paginate(6);
        
    //     // Récupérer tous les retraits de l'utilisateur
    //     $userWithdrawals = Withdrawal::where('user_id', $user_id)->get();
        
    //     // Récupérer le solde de l'utilisateur
    //     $balance = Balance::where('user_id', $user_id)->first();
    
    //     return view('user.finances', compact('userDeposits', 'Withdrawals', 'balance'));
    // }
    
    

}
