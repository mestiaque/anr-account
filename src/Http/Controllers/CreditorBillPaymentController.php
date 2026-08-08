<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Creditor;
use ME\Accounts\Models\CreditorBill;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CreditorBillPaymentController extends Controller
{
    public function index(Request $r)
    {
        $creditorIds = null;
        if ($r->creditor_name || $r->creditor_code) {
            $creditorIds = Creditor::when($r->creditor_name, fn ($q) => $q->where(function ($q) use ($r) {
                    $q->where('name', 'LIKE', '%' . $r->creditor_name . '%')
                        ->orWhere('company_name', 'LIKE', '%' . $r->creditor_name . '%');
                }))
                ->when($r->creditor_code, fn ($q) => $q->where('code', 'LIKE', '%' . $r->creditor_code . '%'))
                ->pluck('id');
        }

        $bills = CreditorBill::with('creditor')
            ->when($creditorIds !== null, fn ($q) => $q->whereIn('creditor_id', $creditorIds))
            ->when($r->title, fn ($q) => $q->where(function ($q) use ($r) {
                $q->where('title', 'LIKE', '%' . $r->title . '%')
                    ->orWhere('bill_no', 'LIKE', '%' . $r->title . '%');
            }))
            ->when($r->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $r->startDate))
            ->when($r->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $r->endDate))
            ->get()
            ->map(fn ($bill) => (object) [
                'date' => $bill->transaction_date,
                'creditor' => $bill->creditor,
                'title' => $bill->bill_no . ' - ' . $bill->title,
                'description' => $bill->description,
                'credit' => (float) $bill->amount,
                'debit' => 0,
            ])
            ->toBase();

        $payments = CreditorBillPayment::with('creditor')
            ->when($creditorIds !== null, fn ($q) => $q->whereIn('creditor_id', $creditorIds))
            ->when($r->title, fn ($q) => $q->where('payment_no', 'LIKE', '%' . $r->title . '%'))
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id))
            ->when($r->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $r->startDate))
            ->when($r->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $r->endDate))
            ->get()
            ->map(fn ($payment) => (object) [
                'date' => $payment->transaction_date,
                'creditor' => $payment->creditor,
                'title' => $payment->payment_no,
                'description' => $payment->description,
                'credit' => 0,
                'debit' => (float) $payment->amount,
            ])
            ->toBase();

        $merged = $bills->merge($payments)->sortByDesc('date')->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $ledgerEntries = new LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $r->query()]
        );

        $totalBills = $bills->sum('credit');
        $totalPayments = $payments->sum('debit');

        $accountMethods = Account::where('status', 'active')->where('owner', Auth::id())->orderBy('name')->get();
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $creditors = Creditor::where('status', 'active')->orderBy('name')->get();

        return view('erp-accounts::accounts.creditorBillPayments', compact(
            'ledgerEntries', 'totalBills', 'totalPayments', 'accountMethods', 'filterAccounts', 'paymentMethods', 'creditors'
        ));
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
        // dd($account);

        if ($r->amount > $account->current_balance) {
            Session()->flash('error', 'Account Balance Are Not Available');
            return redirect()->back();
        }
        $creditor = Creditor::findOrFail($r->creditor_id);

        $transactionDate = $r->created_at ?: Carbon::now();

        // category_id=0 is not a real expense category — it marks this Expense as a
        // display-only shadow row created alongside a creditor bill payment (matches
        // the legacy-import convention, see MigrateLegacyCommand::migrateExpenses).
        $expense = Expense::create([
            'category_id' => 0,
            'payment_method_id' => $r->payment,
            'account_id' => $account->id,
            'amount' => $r->amount,
            'description' => $r->description,
            'company_name' => $creditor->company_name ?: $creditor->name,
            'receiver_name' => $creditor->name,
            'status' => 'active',
            'transaction_date' => $transactionDate,
            'addedby_id' => Auth::id(),
        ]);

        CreditorBillPayment::create([
            'account_id' => $account->id,
            'creditor_id' => $creditor->id,
            'expense_id' => $expense->id,
            'purchase_id' => $r->purchase_id,
            'payment_method_id' => $r->payment,
            'amount' => $r->amount,
            'description' => $r->description,
            'status' => 'success',
            'transaction_date' => $transactionDate,
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Creditor bill payment successfully recorded');
        return redirect()->back();
    }
}
