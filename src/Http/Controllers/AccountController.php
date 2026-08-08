<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Transaction;
use App\Models\User;
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

        $adminUsers = User::filterByType('customer')->where('status', 1)->orderBy('name')->select(['id', 'name', 'mobile', 'email'])->get();

        return view('erp-accounts::accounts.accountsMethods', compact('accounts', 'adminUsers'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:1000',
            'account_owner' => 'required|numeric',
        ]);

        Account::create([
            'name' => $r->name,
            'description' => $r->description,
            'opening_balance' => 0,
            'status' => 'active',
            'owner' => $r->account_owner,
            'created_by' => Auth::id(),
        ]);

        Session()->flash('success', 'Account successfully created');
        return redirect()->back();
    }

    public function update(Request $r, Account $account)
    {
        $r->validate([
            'name' => 'required|max:100',
            'description' => 'nullable|max:1000',
            'account_owner' => 'required|numeric',
            'created_at' => 'required|date',
        ]);

        $account->name = $r->name;
        $account->description = $r->description;
        $account->owner = $r->account_owner;
        $account->status = $r->status ? 'active' : 'inactive';
        $account->created_at = $r->created_at;
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

        return view('erp-accounts::accounts.accountsMethodsView', compact('method', 'openingBalance', 'availableBalance', 'transections', 'from', 'to'));
    }

    protected function balanceAsOf(Account $account, Carbon $before): float
    {
        return (float) Transaction::where('account_id', $account->id)
            ->where('status', 'success')
            ->whereDate('transaction_date', '<', $before)
            ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance') ?? 0;
    }

    public function statement(Request $r)
    {
        $accounts = Account::where('status', 'active')->orderBy('name')->get();
        $accountId = $r->account_id ?? $accounts->first()?->id;
        $method = Account::find($accountId);

        $from = $r->startDate ? Carbon::parse($r->startDate) : Carbon::now()->subDays(30);
        $to = $r->endDate ? Carbon::parse($r->endDate) : Carbon::now();

        $openingBalance = 0;
        $debetTotal = 0;
        $creditTotal = 0;
        $transections = collect();

        if ($method) {
            $openingBalance = $this->balanceAsOf($method, $from);

            $transections = Transaction::with(['account'])
                ->where('account_id', $method->id)
                ->where('status', 'success')
                ->whereDate('transaction_date', '>=', $from)
                ->whereDate('transaction_date', '<=', $to)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            $balance = $openingBalance;
            $transections->each(function (Transaction $t) use (&$balance, &$debetTotal, &$creditTotal) {
                if ($t->direction === 'credit') {
                    $balance += $t->amount;
                    $creditTotal += $t->amount;
                } else {
                    $balance -= $t->amount;
                    $debetTotal += $t->amount;
                }
                $t->running_balance = $balance;
                $t->reference = $this->referenceLabel($t);
                $t->particulars = $this->particularsFor($t);
            });
        }

        return view('erp-accounts::accounts.accountsStatement', compact('accounts', 'method', 'openingBalance', 'transections', 'from', 'to', 'debetTotal', 'creditTotal'));
    }

    protected function referenceLabel(Transaction $t): string
    {
        return match ($t->source_type) {
            'expense' => 'Expense',
            'iou' => 'I.O.U',
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer Balance',
            'creditor_bill_payment' => 'Creditor Bill',
            default => 'Unknown',
        };
    }

    protected function particularsFor(Transaction $t): string
    {
        $source = $t->source;

        return match ($t->source_type) {
            'expense' => $source
                ? "Company: {$source->company_name} | Receiver: {$source->receiver_name}" . ($source->description ? " | Desc: {$source->description}" : '')
                : 'N/A',
            'iou' => $source
                ? "Company: {$source->company_name} | Receiver: {$source->receiver_name}" . ($source->description ? " | Desc: {$source->description}" : '')
                : 'N/A',
            'deposit' => $source
                ? 'Received From: ' . ($source->received_from ?? 'N/A') . ($source->description ? " | Desc: {$source->description}" : '')
                : 'Deposit',
            'withdrawal' => $source?->description ?: 'Withdrawal',
            'transfer' => $source
                ? ($t->direction === 'debit' ? 'To: ' . ($source->toAccount?->name ?? 'N/A') : 'From: ' . ($source->fromAccount?->name ?? 'N/A'))
                : 'Transfer',
            'creditor_bill_payment' => $source
                ? 'Creditor: ' . ($source->creditor?->name ?? 'N/A') . ($source->description ? " | Desc: {$source->description}" : '')
                : 'N/A',
            default => $t->transaction_no,
        };
    }
}
