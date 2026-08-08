<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function index(Request $r)
    {
        $withdrawals = Withdrawal::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('withdrawal_no', 'LIKE', '%' . $r->search . '%'))
            ->when($r->account, fn ($q) => $q->where('account_id', $r->account))
            ->latest()
            ->paginate(10);

        return view(adminTheme() . 'accounts.withdrawal', compact('withdrawals'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'account' => 'required|numeric',
            'payment' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'bank_name' => 'nullable|max:100',
            'created_at' => 'nullable|date',
        ]);

        $account = Account::findOrFail($r->account);

        if ($r->amount > $account->current_balance) {
            Session()->flash('error', 'This Account balance Are Not available');
            return redirect()->back();
        }

        Withdrawal::create([
            'account_id' => $account->id,
            'payment_method_id' => $r->payment,
            'amount' => $r->amount,
            'bank_name' => $r->bank_name,
            'description' => $r->description,
            'status' => 'success',
            'transaction_date' => $r->created_at ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Withdrawal successfully added');
        return redirect()->back();
    }

    public function update(Request $r, Withdrawal $withdrawal)
    {
        $r->validate([
            'payment' => 'nullable|numeric',
            'bank_name' => 'nullable|max:100',
        ]);

        $withdrawal->payment_method_id = $r->payment;
        $withdrawal->bank_name = $r->bank_name;
        $withdrawal->description = $r->description;
        $withdrawal->editedby_id = Auth::id();
        $withdrawal->save();

        Session()->flash('success', 'Withdrawal successfully updated');
        return redirect()->back();
    }

    public function destroy(Withdrawal $withdrawal)
    {
        $withdrawal->delete();

        Session()->flash('success', 'Withdrawal successfully deleted');
        return redirect()->back();
    }
}
