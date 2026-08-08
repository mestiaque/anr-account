<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $r)
    {
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

        return view(adminTheme() . 'expenses.expensesAll', compact('expenses', 'from', 'to'));
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
            'created_at' => 'nullable|date',
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
            'transaction_date' => $r->created_at ?: Carbon::now(),
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
}
