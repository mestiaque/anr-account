<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\BalanceTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceTransferController extends Controller
{
    public function index(Request $r)
    {
        $transections = BalanceTransfer::when($r->account, fn ($q) => $q->where('from_account_id', $r->account)->orWhere('to_account_id', $r->account))
            ->latest()
            ->paginate(10);

        $accountMethods = Account::where('status', 'active')->orderBy('name')->get();

        return view('erp-accounts::accounts.balanceTransfers', compact('transections', 'accountMethods'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'form_account' => 'required|numeric',
            'to_account' => 'required|numeric|different:form_account',
            'amount' => 'required|numeric',
            'created_at' => 'nullable|date',
        ]);

        $fromAccount = Account::findOrFail($r->form_account);
        Account::findOrFail($r->to_account);

        if ($r->amount > $fromAccount->current_balance) {
            Session()->flash('error', 'Account Balance Are Not Available');
            return redirect()->back();
        }

        BalanceTransfer::create([
            'from_account_id' => $r->form_account,
            'to_account_id' => $r->to_account,
            'amount' => $r->amount,
            'description' => $r->description,
            'status' => 'success',
            'transaction_date' => $r->created_at ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Balance successfully transferred');
        return redirect()->back();
    }
}
