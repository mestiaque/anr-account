<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Branch;
use ME\Accounts\Models\Iou;
use ME\Accounts\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IouController extends Controller
{
    public function index(Request $r)
    {
        if ($r->action == 5 && $r->checkid) {
            Iou::whereIn('id', $r->checkid)->get()->each->delete();
            Session()->flash('success', 'Action Successfully Completed!');
            return redirect()->back();
        }

        $expenseIou = Iou::whereNotIn('status', ['temp', 'completed'])
            ->when($r->search, fn ($q) => $q->where('employee_id', 'LIKE', '%' . $r->search . '%')
                ->orWhere('company_name', 'LIKE', '%' . $r->search . '%')
                ->orWhere('receiver_name', 'LIKE', '%' . $r->search . '%'))
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id))
            ->latest()
            ->get();

        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();
        $accountMethods = Account::where('status', 'active')->where('owner', Auth::id())->orderBy('name')->get();
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $users = User::where('status', 1)->orderBy('name')->get();

        return view('erp-accounts::expenses.expensesIOU', compact('expenseIou', 'paymentMethods', 'accountMethods', 'filterAccounts', 'branches', 'users'));
    }

    public function completed(Request $r)
    {
        $query = Iou::where('status', 'completed')
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id));

        $totalAmount = (clone $query)->sum('amount');
        $completedIou = $query->orderBy('updated_at', 'desc')->paginate(50);
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();

        return view('erp-accounts::expenses.completedIOU', compact('completedIou', 'totalAmount', 'filterAccounts'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'employee_id' => 'nullable|max:100',
            'payment' => 'nullable|numeric',
            'account' => 'required|numeric',
            'branch_id' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'company_name' => 'nullable|max:100',
            'receiver_name' => 'nullable|max:100',
            'created_at' => 'nullable|date',
        ]);

        $account = Account::findOrFail($r->account);

        if ($r->amount > $account->current_balance) {
            Session()->flash('error', 'Account Balance Are Not Available');
            return redirect()->back();
        }

        $employeeUser = $r->employee_id
            ? User::where('status', 1)->where('employee_id', $r->employee_id)->first()
            : null;

        Iou::create([
            'employee_id' => $r->employee_id,
            'user_id' => $employeeUser?->id,
            'payment_method_id' => $r->payment,
            'account_id' => $account->id,
            'branch_id' => $r->branch_id,
            'amount' => $r->amount,
            'description' => $r->description,
            'company_name' => $r->company_name,
            'receiver_name' => $r->receiver_name,
            'status' => 'pending',
            'transaction_date' => $r->created_at ?: Carbon::now(),
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'I.O.U successfully added');
        return redirect()->back();
    }

    public function update(Request $r, Iou $iou)
    {
        $r->validate([
            'employee_id' => 'nullable|max:100',
            'payment' => 'nullable|numeric',
            'branch_id' => 'nullable|numeric',
            'amount' => 'required|numeric',
            'company_name' => 'nullable|max:100',
            'receiver_name' => 'nullable|max:100',
        ]);

        $employeeUser = $r->employee_id
            ? User::where('status', 1)->where('employee_id', $r->employee_id)->first()
            : null;

        $iou->employee_id = $r->employee_id;
        $iou->user_id = $employeeUser?->id;
        $iou->payment_method_id = $r->payment;
        $iou->branch_id = $r->branch_id;
        $iou->amount = $r->amount ?: 0;
        $iou->description = $r->description;
        $iou->company_name = $r->company_name;
        $iou->receiver_name = $r->receiver_name;
        $iou->status = $r->status ? 'completed' : 'pending';
        $iou->editedby_id = Auth::id();
        $iou->save();

        Session()->flash('success', 'I.O.U successfully updated');
        return redirect()->back();
    }

    public function destroy(Iou $iou)
    {
        $iou->delete();

        Session()->flash('success', 'I.O.U successfully deleted');
        return redirect()->back();
    }

    public function searchEmployee(Request $r)
    {
        $employee = User::where('status', 1)->whereNotNull('employee_id')->where('employee_id', $r->search)->first();

        return response()->json([
            'status' => (bool) $employee,
            'name' => $employee?->name,
        ]);
    }

    public function report(Request $r)
    {
        $from = $r->startDate ? Carbon::parse($r->startDate) : Carbon::now();
        $to = $r->endDate ? Carbon::parse($r->endDate) : Carbon::now();

        $expenses = Iou::latest()->whereNotIn('status', ['temp', 'completed'])
            ->when($r->search, fn ($q) => $q->where('employee_id', $r->search))
            ->when($r->branch_id, fn ($q) => $q->where('branch_id', $r->branch_id))
            ->when($r->employee, fn ($q) => $q->where(function ($sub) use ($r) {
                $sub->where('employee_id', 'like', '%' . $r->employee . '%')
                    ->orWhereHas('employeeUser', fn ($query) => $query->where('name', 'like', '%' . $r->employee . '%'));
            }))
            ->when($r->account_id, fn ($q) => $q->where('account_id', $r->account_id))
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->get();

        $users = User::where('status', 1)->orderBy('name')->get();
        $filterAccounts = Account::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        return view('erp-accounts::expenses.expenseIOUReports', compact('expenses', 'users', 'from', 'to', 'branches', 'filterAccounts'));
    }
}
