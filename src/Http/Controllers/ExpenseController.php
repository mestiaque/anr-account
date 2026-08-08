<?php

namespace ME\Accounts\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ME\Accounts\Models\Account;
use ME\Accounts\Models\Branch;
use ME\Accounts\Models\Creditor;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\ExpenseCategory;
use ME\Accounts\Models\PaymentMethod;

class ExpenseController extends Controller
{
    public function index(Request $r)
    {
        if ($r->action == 5 && $r->checkid) {
            Expense::whereIn('id', $r->checkid)->get()->each->delete();
            Session()->flash('success', 'Action Successfully Completed!');
            return redirect()->back();
        }

        $from = $r->startDate ?? Carbon::now()->format('Y-m-d');
        $to = $r->endDate ?? Carbon::now()->format('Y-m-d');

        $expenses = Expense::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('expense_no', 'LIKE', '%' . ltrim($r->search, '0') . '%'))
            ->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->when($r->expense_type, fn ($q) => $q->where('category_id', $r->expense_type))
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id))
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->latest()
            ->paginate(25)
            ->appends($r->all());

        $report = [
            'today_expenses' => numberFormat(Expense::where('status', '<>', 'temp')->whereDate('transaction_date', Carbon::today())->sum('amount'), 2),
            'monthly_expenses' => numberFormat(Expense::where('status', '<>', 'temp')->whereMonth('transaction_date', Carbon::now()->month)->whereYear('transaction_date', Carbon::now()->year)->sum('amount'), 2),
            'filtered_expenses' => numberFormat(Expense::where('status', '<>', 'temp')->whereDate('transaction_date', '>=', $from)->whereDate('transaction_date', '<=', $to)->sum('amount'), 2),
            'filtered_total' => numberFormat((clone $expenses)->sum('amount'), 2),
        ];

        $expenseTypes = ExpenseCategory::where('status', 'active')->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $accountMethods = Account::where('status', 'active')->where('owner', Auth::id())->orderBy('name')->get();
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $creditors = Creditor::all();
        $lastAudit = Expense::whereNotNull('audit_at')->latest()->first();

        return view('erp-accounts::expenses.expensesAll', compact('expenses', 'report', 'expenseTypes', 'paymentMethods', 'accountMethods', 'branches', 'to', 'from', 'lastAudit', 'filterAccounts', 'creditors'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'expense_type' => 'nullable|numeric',
            'payment' => 'nullable|numeric',
            'account' => 'required|numeric',
            'branch_id' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'company_name' => 'required|max:100',
            'receiver_name' => 'required|max:100',
            'receiver_mobile' => 'nullable|max:100',
            'transaction_date' => 'nullable|date',
        ]);

        $account = Account::findOrFail($r->account);

        if ($r->amount > $account->current_balance) {
            Session()->flash('error', 'Account Balance Are Not Available');
            return redirect()->back();
        }

        Expense::create([
            'category_id' => $r->expense_type,
            'payment_method_id' => $r->payment,
            'account_id' => $account->id,
            'branch_id' => $r->branch_id,
            'amount' => $r->amount,
            'description' => $r->description,
            'company_name' => $r->company_name,
            'receiver_name' => $r->receiver_name,
            'receiver_mobile' => $r->receiver_mobile,
            'status' => 'active',
            'transaction_date' => $r->transaction_date ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Expense successfully added');
        return redirect()->back();
    }

    public function update(Request $r, Expense $expense)
    {
        $r->validate([
            'expense_type' => 'nullable|numeric',
            'payment' => 'nullable|numeric',
            'branch_id' => 'nullable|numeric',
            'company_name' => 'required|max:100',
            'receiver_name' => 'required|max:100',
            'amount' => 'required|numeric',
            'transaction_date' => 'nullable|date',
        ]);

        $expense->category_id = $r->expense_type;
        $expense->payment_method_id = $r->payment;
        $expense->branch_id = $r->branch_id;
        $expense->amount = $r->amount;
        $expense->description = $r->description;
        $expense->company_name = $r->company_name;
        $expense->receiver_name = $r->receiver_name;
        $expense->receiver_mobile = $r->receiver_mobile;
        $expense->status = $r->status ? 'active' : 'inactive';
        $expense->transaction_date = $r->transaction_date ?: $expense->transaction_date;
        $expense->editedby_id = Auth::id();
        $expense->save();

        Session()->flash('success', 'Expense successfully updated');
        return redirect()->back();
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        Session()->flash('success', 'Expense successfully deleted');
        return redirect()->back();
    }

    public function report(Request $r)
    {
        $from = $r->startDate ? Carbon::parse($r->startDate) : Carbon::now();
        $to = $r->endDate ? Carbon::parse($r->endDate) : Carbon::now();

        $expenses = Expense::latest()->where('status', 'active')
            ->when($r->search, fn ($q) => $q->where('expense_no', 'LIKE', '%' . $r->search . '%'))
            ->when($r->expense_type, fn ($q) => $q->where('category_id', $r->expense_type))
            ->when($r->branch_id, fn ($q) => $q->where('branch_id', $r->branch_id))
            ->when($r->method, fn ($q) => $q->where('payment_method_id', $r->method))
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id))
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->get();

        $expenseTypes = ExpenseCategory::where('status', 'active')->orderBy('name')->get();
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $supplierBill = CreditorBillPayment::whereDate('transaction_date', '>=', $from)->whereDate('transaction_date', '<=', $to)->sum('amount');

        if ($r->summery) {
            return view('erp-accounts::expenses.expenseSummeryReports', compact('expenses', 'expenseTypes', 'from', 'to', 'branches', 'supplierBill', 'filterAccounts'));
        }

        return view('erp-accounts::expenses.expenseReports', compact('expenses', 'expenseTypes', 'from', 'to', 'branches', 'supplierBill', 'filterAccounts'));
    }

    public function audit(Request $r)
    {
        Expense::whereIn('id', $r->audit_data ?? [])->whereNull('audit_at')->get()->each(function (Expense $expense) {
            $expense->audit_at = now();
            $expense->audit_by = Auth::id();
            $expense->save();
        });

        Session()->flash('success', 'You have successfully audited the report');
        return redirect()->back();
    }
}
