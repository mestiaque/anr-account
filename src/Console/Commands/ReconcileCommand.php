<?php

namespace ME\Accounts\Console\Commands;

use Illuminate\Console\Command;
use ME\Accounts\Models\Account;

class ReconcileCommand extends Command
{
    protected $signature = 'ac:reconcile {account? : Specific ac_accounts.id to check} {--fix : Correct the cached current_balance to match the transaction-derived truth}';
    protected $description = 'Compare each account\'s cached current_balance to SUM(ac_transactions), report drift, optionally fix it.';

    public function handle(): int
    {
        $accounts = $this->argument('account')
            ? Account::where('id', $this->argument('account'))->get()
            : Account::all();

        if ($accounts->isEmpty()) {
            $this->error('No matching account(s) found.');
            return self::FAILURE;
        }

        $anyMismatch = false;

        $this->table(
            ['Account', 'Cached', 'Computed', 'Diff', 'Status'],
            $accounts->map(function (Account $account) use (&$anyMismatch) {
                $cached = (float) $account->current_balance;
                $computed = $this->computedBalance($account);
                $diff = round($computed - $cached, 2);

                if ($diff != 0.0) {
                    $anyMismatch = true;

                    if ($this->option('fix')) {
                        $account->current_balance = $computed;
                        $account->save();
                    }
                }

                return [
                    $account->name,
                    number_format($cached, 2),
                    number_format($computed, 2),
                    number_format($diff, 2),
                    $diff == 0.0 ? 'OK' : ($this->option('fix') ? 'FIXED' : 'MISMATCH'),
                ];
            })
        );

        if (!$anyMismatch) {
            $this->info('✅ All accounts are in sync.');
        } elseif (!$this->option('fix')) {
            $this->warn('⚠️  Mismatches found. Re-run with --fix to correct them.');
        }

        return self::SUCCESS;
    }

    protected function computedBalance(Account $account): float
    {
        return round((float) $account->transactions()
            ->where('status', 'success')
            ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance'), 2) ?: 0.0;
    }
}
