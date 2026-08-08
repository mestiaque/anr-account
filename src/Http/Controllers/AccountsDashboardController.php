<?php

namespace ME\Accounts\Http\Controllers;

use Carbon\Carbon;
use ME\Accounts\Models\Account;
use ME\Accounts\Models\Creditor;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\ExpenseCategory;
use ME\Accounts\Models\Iou;

class AccountsDashboardController extends Controller
{
    public function index()
    {
        return view('erp-accounts::dashboard');
    }

    public static function stats(): array
    {
        try {
            $today = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();

            $accounts = Account::where('status', 'active')->orderByDesc('current_balance')->get();
            $totalAccounts = $accounts->count();
            $totalBalance = $accounts->sum('current_balance');

            $todayExpenses = Expense::whereDate('transaction_date', $today)->sum('amount');
            $monthExpenses = Expense::whereDate('transaction_date', '>=', $monthStart)->sum('amount');

            $pendingIous = Iou::where('status', 'pending');
            $pendingIouCount = (clone $pendingIous)->count();
            $pendingIouAmount = (clone $pendingIous)->sum('amount');

            $creditors = Creditor::where('status', 'active')->get();
            $totalCreditorsDue = $creditors->sum(fn ($c) => $c->bills()->sum('amount') - $c->billPayments()->sum('amount'));

            // Last 30 days daily expense trend
            $last30 = collect(range(0, 29))->map(function ($i) {
                $date = Carbon::today()->subDays(29 - $i);
                return [
                    'date' => $date->format('d M'),
                    'amount' => (float) Expense::whereDate('transaction_date', $date)->sum('amount'),
                ];
            });

            // Expense by category (this month)
            $expenseByCategory = ExpenseCategory::where('status', 'active')
                ->get()
                ->map(fn ($cat) => [
                    'name' => $cat->name,
                    'amount' => (float) Expense::where('category_id', $cat->id)->whereDate('transaction_date', '>=', $monthStart)->sum('amount'),
                ])
                ->filter(fn ($row) => $row['amount'] > 0)
                ->sortByDesc('amount')
                ->take(6)
                ->values();

            $recentExpenses = Expense::with(['account', 'category'])->latest()->take(6)->get();
            $recentIous = Iou::with('account')->where('status', 'pending')->latest()->take(6)->get();

            return compact(
                'accounts', 'totalAccounts', 'totalBalance',
                'todayExpenses', 'monthExpenses',
                'pendingIouCount', 'pendingIouAmount',
                'totalCreditorsDue',
                'last30', 'expenseByCategory',
                'recentExpenses', 'recentIous'
            );
        } catch (\Throwable $e) {
            return [
                'accounts' => collect(), 'totalAccounts' => 0, 'totalBalance' => 0,
                'todayExpenses' => 0, 'monthExpenses' => 0,
                'pendingIouCount' => 0, 'pendingIouAmount' => 0,
                'totalCreditorsDue' => 0,
                'last30' => collect(), 'expenseByCategory' => collect(),
                'recentExpenses' => collect(), 'recentIous' => collect(),
            ];
        }
    }
}
