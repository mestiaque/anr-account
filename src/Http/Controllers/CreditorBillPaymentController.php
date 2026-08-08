<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditorBillPaymentController extends Controller
{
    public function index(Request $r)
    {
        $payments = CreditorBillPayment::when($r->account, fn ($q) => $q->where('account_id', $r->account))
            ->when($r->creditor_id, fn ($q) => $q->where('creditor_id', $r->creditor_id))
            ->latest()
            ->paginate(25);

        $accountMethods = Account::where('status', 'active')->where('owner', Auth::id())->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $creditors = User::filterByType('supplier')->where('status', 1)->orderBy('name')->get();

        return view('erp-accounts::accounts.creditorBillPayments', compact('payments', 'accountMethods', 'paymentMethods', 'creditors'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'account' => 'required|numeric',
            'creditor_id' => 'required|numeric',
            'purchase_id' => 'nullable|numeric',
            'payment' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'created_at' => 'nullable|date',
        ]);

        $account = Account::findOrFail($r->account);

        if ($r->amount > $account->current_balance) {
            Session()->flash('error', 'Account Balance Are Not Available');
            return redirect()->back();
        }

        CreditorBillPayment::create([
            'account_id' => $account->id,
            'creditor_id' => $r->creditor_id,
            'purchase_id' => $r->purchase_id,
            'payment_method_id' => $r->payment,
            'amount' => $r->amount,
            'description' => $r->description,
            'status' => 'success',
            'transaction_date' => $r->created_at ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Creditor bill payment successfully recorded');
        return redirect()->back();
    }
}
