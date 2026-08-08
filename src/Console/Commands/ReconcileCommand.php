<?php

namespace ME\Accounts\Console\Commands;

use Illuminate\Console\Command;
use ME\Accounts\Models\Account;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\Deposit;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\Iou;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Models\Withdrawal;
use ME\Accounts\Services\LedgerService;

class ReconcileCommand extends Command
{
    protected $signature = 'ac:reconcile {account? : Specific ac_accounts.id to check} {--fix : Correct the cached current_balance and backfill orphaned ledger entries}';
    protected $description = 'Compare each account\'s cached current_balance to SUM(ac_transactions), and check every Expense/IOU/Deposit/Withdrawal/Creditor Bill Payment has a matching ledger row. Report drift and orphans, optionally fix both.';

    protected array $orphanSources = [
        'expense' => Expense::class,
        'iou' => Iou::class,
        'deposit' => Deposit::class,
        'withdrawal' => Withdrawal::class,
        'creditor_bill_payment' => CreditorBillPayment::class,
    ];

    public function handle(): int
    {
        $anyIssue = $this->checkBalances() || $this->checkOrphans();

        if (!$anyIssue) {
            $this->info('✅ Everything is in sync — no balance drift, no orphaned records.');
        } elseif (!$this->option('fix')) {
            $this->warn('⚠️  Issues found. Re-run with --fix to correct them.');
        }

        return self::SUCCESS;
    }

    protected function checkBalances(): bool
    {
        $accounts = $this->argument('account')
            ? Account::where('id', $this->argument('account'))->get()
            : Account::all();

        if ($accounts->isEmpty()) {
            $this->error('No matching account(s) found.');
            return true;
        }

        $anyMismatch = false;

        $this->info('Balance check:');
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

        return $anyMismatch;
    }

    /**
     * Every Expense/IOU/Deposit/Withdrawal/Creditor Bill Payment must have a matching
     * ac_transactions row, or its amount was never actually deducted/credited from the
     * account balance. This has happened twice already this session (once from a missing
     * method, once from a botched --fresh migration) — orphans are checked on every run.
     */
    protected function checkOrphans(): bool
    {
        $anyOrphans = false;
        $rows = [];

        foreach ($this->orphanSources as $sourceType => $modelClass) {
            $orphaned = [];

            $modelClass::when($this->argument('account'), fn ($q) => $q->where('account_id', $this->argument('account')))
                ->orderBy('created_at')
                ->chunk(300, function ($chunk) use (&$orphaned, $sourceType) {
                    foreach ($chunk as $record) {
                        if (!Transaction::where('source_type', $sourceType)->where('source_id', $record->id)->exists()) {
                            $orphaned[] = $record;
                        }
                    }
                });

            if (empty($orphaned)) {
                continue;
            }

            $anyOrphans = true;
            $sum = collect($orphaned)->sum('amount');
            $rows[] = [$sourceType, count($orphaned), number_format($sum, 2)];

            if ($this->option('fix')) {
                foreach ($orphaned as $record) {
                    $this->fixOrphan($sourceType, $record);
                }
            }
        }

        if ($anyOrphans) {
            $this->newLine();
            $this->info('Orphaned records (no ledger entry — balance never adjusted):');
            $this->table(['Source Type', 'Count', 'Total Amount'], $rows);
            if ($this->option('fix')) {
                $this->info('→ Backfilled ledger entries and adjusted account balances for all orphans above.');
            }
        }

        return $anyOrphans;
    }

    protected function fixOrphan(string $sourceType, $record): void
    {
        $account = Account::find($record->account_id);
        if (!$account) {
            return;
        }

        if ($sourceType === 'iou' && $record->status === 'completed') {
            $txn = LedgerService::record($account, 'iou', $record->id, 'debit', (float) $record->amount, $record->transaction_date, $record->addedby_id ?? null, 'success');
            LedgerService::reverse($txn);
            return;
        }

        $direction = $sourceType === 'deposit' ? 'credit' : 'debit';

        LedgerService::record($account, $sourceType, $record->id, $direction, (float) $record->amount, $record->transaction_date, $record->addedby_id ?? null, 'success');
    }

    protected function computedBalance(Account $account): float
    {
        return round((float) $account->transactions()
            ->where('status', 'success')
            ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as balance")
            ->value('balance'), 2) ?: 0.0;
    }
}
