<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    public function index(Request $r)
    {
        $deposits = Deposit::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('deposit_no', 'LIKE', '%' . $r->search . '%'))
            ->when($r->account, fn ($q) => $q->where('account_id', $r->account))
            ->latest()
            ->paginate(10);

        return view(adminTheme() . 'accounts.deposits', compact('deposits'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'account' => 'required|numeric',
            'amount' => 'required|numeric',
            'received_method' => 'nullable|max:100',
            'received_from' => 'nullable|max:100',
            'created_at' => 'nullable|date',
        ]);

        $account = Account::findOrFail($r->account);

        Deposit::create([
            'account_id' => $account->id,
            'amount' => $r->amount,
            'received_from' => $r->received_from,
            'received_method' => $r->received_method,
            'bank_name' => $r->bank_name,
            'description' => $r->description,
            'status' => 'pending',
            'transaction_date' => $r->created_at ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Deposit successfully created and pending approval');
        return redirect()->back();
    }

    public function approve(Deposit $deposit)
    {
        $deposit->status = 'success';
        $deposit->editedby_id = Auth::id();
        $deposit->save();

        Session()->flash('success', 'Deposit approved and account updated successfully');
        return redirect()->back();
    }

    public function update(Request $r, Deposit $deposit)
    {
        $r->validate([
            'received_method' => 'nullable|max:100',
            'received_from' => 'nullable|max:100',
        ]);

        $deposit->received_method = $r->received_method;
        $deposit->received_from = $r->received_from;
        $deposit->description = $r->description;
        $deposit->bank_name = $r->bank_name;
        $deposit->editedby_id = Auth::id();
        $deposit->save();

        Session()->flash('success', 'Deposit successfully updated');
        return redirect()->back();
    }

    public function destroy(Deposit $deposit)
    {
        $deposit->delete();

        Session()->flash('success', 'Deposit successfully deleted');
        return redirect()->back();
    }
}
