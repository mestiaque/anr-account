<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index(Request $r)
    {
        $accounts = Account::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('name', 'LIKE', '%' . $r->search . '%'))
            ->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->latest()
            ->paginate(25)
            ->appends(['search' => $r->search, 'status' => $r->status]);

        return view(adminTheme() . 'accounts.accountsMethods', compact('accounts'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:1000',
            'opening_balance' => 'nullable|numeric',
        ]);

        Account::create([
            'name' => $r->name,
            'description' => $r->description,
            'opening_balance' => $r->opening_balance ?: 0,
            'status' => 'active',
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Account successfully created');
        return redirect()->back();
    }

    public function update(Request $r, Account $account)
    {
        $r->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:1000',
        ]);

        $account->name = $r->name;
        $account->description = $r->description;
        $account->status = $r->status ? 'active' : 'inactive';
        $account->editedby_id = Auth::id();
        $account->save();

        Session()->flash('success', 'Account successfully updated');
        return redirect()->back();
    }

    public function destroy(Account $account)
    {
        $account->status = 'inactive';
        $account->save();

        Session()->flash('success', 'Account successfully deactivated');
        return redirect()->back();
    }

    public function show(Request $r, Account $account)
    {
        $from = $r->startDate ? Carbon::parse($r->startDate) : Carbon::now()->subDays(30);
        $to = $r->endDate ? Carbon::parse($r->endDate) : Carbon::now();

        $openingBalance = $this->balanceAsOf($account, $from);

        $transections = Transaction::where('account_id', $account->id)
            ->where('status', 'success')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $balance = $openingBalance;
        $transections->each(function (Transaction $t) use (&$balance) {
            $balance = $t->direction === 'credit' ? $balance + $t->amount : $balance - $t->amount;
            $t->running_balance = $balance;
        });

        $availableBalance = $balance;
        $method = $account; // keep legacy blade variable name

        return view(adminTheme() . 'accounts.accountsMethodsView', compact('method', 'openingBalance', 'availableBalance', 'transections', 'from', 'to'));
    }

    protected function balanceAsOf(Account $account, Carbon $before): float
    {
        return (float) Transaction::where('account_id', $account->id)
            ->where('status', 'success')
            ->whereDate('transaction_date', '<', $before)
            ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance') ?? 0;
    }
}
