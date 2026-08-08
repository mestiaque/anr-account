<?php

namespace ME\Accounts\Console\Commands;

use App\Models\Attribute as LegacyAttribute;
use App\Models\Expense as LegacyExpense;
use App\Models\ExpenseIou as LegacyExpenseIou;
use App\Models\Transaction as LegacyTransaction;
use App\Models\User as LegacyUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ME\Accounts\Services\SequenceGenerator;

class MigrateLegacyCommand extends Command
{
    protected $signature = 'ac:migrate-legacy {--fresh : Wipe previously migrated ac_* rows and re-run from scratch}';
    protected $description = 'One-time, idempotent migration of legacy Attribute/Transaction/Expense/ExpenseIou data into the new ac_* tables.';

    public function handle(): int
    {
        // wipe() and the repopulation must be one atomic unit — if wipe() ran
        // outside this transaction and repopulation later failed, the wipe
        // would stay committed while the rebuild rolled back, leaving every
        // ac_* table permanently empty. (This actually happened once — see
        // the "Data recovery" note in this file's git history.)
        DB::transaction(function () {
            if ($this->option('fresh')) {
                $this->wipe();
            }

            $this->migrateLookups();
            $this->migrateCreditors();
            $this->migrateExpenses();
            $this->migrateIous();
            $this->migrateDepositsWithdrawalsTransfersAndBills();
            $this->recomputeBalances();
        });

        $this->verify();

        return self::SUCCESS;
    }

    protected function wipe(): void
    {
        foreach (['ac_transactions', 'ac_expenses', 'ac_ious', 'ac_deposits', 'ac_withdrawals', 'ac_balance_transfers', 'ac_creditor_bill_payments', 'ac_creditor_bills', 'ac_creditors', 'ac_accounts', 'ac_payment_methods', 'ac_expense_categories', 'ac_branches'] as $table) {
            DB::table($table)->delete();
        }
    }

    protected function migrateLookups(): void
    {
        $map = [0 => 'ac_branches', 5 => 'ac_expense_categories', 9 => 'ac_payment_methods', 10 => 'ac_accounts'];

        foreach ($map as $legacyType => $table) {
            LegacyAttribute::where('type', $legacyType)->orderBy('id')->chunk(200, function ($rows) use ($table, $legacyType) {
                foreach ($rows as $row) {
                    if (DB::table($table)->where('legacy_id', $row->id)->exists()) {
                        continue;
                    }

                    $data = [
                        'legacy_id' => $row->id,
                        'name' => $row->name ?: ('#' . $row->id),
                        'status' => $row->status === 'active' ? 'active' : 'inactive',
                        'editedby_id' => $row->editedby_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];

                    if ($table === 'ac_branches') {
                        $data['bn_name'] = $row->bn_name;
                    } else {
                        $data['description'] = $row->description;
                    }

                    if ($table === 'ac_accounts') {
                        $data['opening_balance'] = 0;
                        $data['current_balance'] = 0; // recomputed later from migrated transactions
                        // Legacy addedby_id was already used as the account "owner" for
                        // filtering which accounts a user could pick in expense/IOU forms.
                        $data['owner'] = $row->addedby_id;
                        $data['created_by'] = $row->addedby_id;
                    } else {
                        $data['addedby_id'] = $row->addedby_id;
                    }

                    DB::table($table)->insert($data);
                }
            });
        }
    }

    protected function migrateCreditors(): void
    {
        LegacyUser::filterByType('supplier')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                if (DB::table('ac_creditors')->where('legacy_user_id', $row->id)->exists()) {
                    continue;
                }

                DB::table('ac_creditors')->insert([
                    'legacy_user_id' => $row->id,
                    'name' => $row->name ?: ('#' . $row->id),
                    'company_name' => $row->company_name ?? null,
                    'mobile' => $row->mobile,
                    'email' => $row->email,
                    'status' => $row->status ? 'active' : 'inactive',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        });
    }

    protected function creditorId(?int $legacyUserId): ?int
    {
        if (!$legacyUserId) {
            return null;
        }

        return DB::table('ac_creditors')->where('legacy_user_id', $legacyUserId)->value('id');
    }

    protected function accountId(?int $legacyAttributeId): ?int
    {
        if (!$legacyAttributeId) {
            return null;
        }

        return DB::table('ac_accounts')->where('legacy_id', $legacyAttributeId)->value('id');
    }

    protected function paymentMethodId(?int $legacyAttributeId): ?int
    {
        if (!$legacyAttributeId) {
            return null;
        }

        return DB::table('ac_payment_methods')->where('legacy_id', $legacyAttributeId)->value('id');
    }

    protected function migrateExpenses(): void
    {
        LegacyExpense::orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                if (DB::table('ac_expenses')->where('legacy_id', $row->id)->exists()) {
                    continue;
                }

                // category_id=0 is not a real expense category — the legacy creditor-bill-payment
                // flow (PurchasesController::suppliersAction 'bill-payment-store') creates one of
                // these as a display-only shadow row alongside the real type=3 Transaction. The
                // real money movement is migrated separately as an ac_creditor_bill_payments row;
                // importing this one too double-counts the same payment as an expense.
                if ((int) $row->category_id === 0) {
                    continue;
                }

                $accountId = $this->accountId($row->account_id);
                if (!$accountId) {
                    continue;
                }

                DB::table('ac_expenses')->insert([
                    'legacy_id' => $row->id,
                    'expense_no' => SequenceGenerator::next('expense'),
                    'category_id' => DB::table('ac_expense_categories')->where('legacy_id', $row->category_id)->value('id'),
                    'account_id' => $accountId,
                    'payment_method_id' => $this->paymentMethodId($row->method_id),
                    'branch_id' => DB::table('ac_branches')->where('legacy_id', $row->branch_id)->value('id'),
                    'amount' => $row->amount,
                    'company_name' => $row->company_name,
                    'receiver_name' => $row->receiver_name,
                    'receiver_mobile' => $row->receiver_mobile,
                    'description' => $row->description,
                    'status' => $row->status === 'active' ? 'active' : 'inactive',
                    'audit_at' => $row->audit_at,
                    'audit_by' => $row->audit_by,
                    'transaction_date' => $row->created_at,
                    'addedby_id' => $row->addedby_id,
                    'editedby_id' => $row->editedby_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        });
    }

    protected function migrateIous(): void
    {
        LegacyExpenseIou::orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                if (DB::table('ac_ious')->where('legacy_id', $row->id)->exists()) {
                    continue;
                }

                $accountId = $this->accountId($row->account_id);
                if (!$accountId) {
                    continue;
                }

                DB::table('ac_ious')->insert([
                    'legacy_id' => $row->id,
                    'iou_no' => SequenceGenerator::next('iou'),
                    'employee_id' => $row->employee_id,
                    'user_id' => $row->user_id,
                    'account_id' => $accountId,
                    'payment_method_id' => $this->paymentMethodId($row->method_id),
                    'branch_id' => DB::table('ac_branches')->where('legacy_id', $row->branch_id)->value('id'),
                    'amount' => $row->amount,
                    'company_name' => $row->company_name,
                    'receiver_name' => $row->receiver_name,
                    'description' => $row->description,
                    'status' => $row->status === 'completed' ? 'completed' : 'pending',
                    'transaction_date' => $row->created_at,
                    'addedby_id' => $row->addedby_id,
                    'editedby_id' => $row->editedby_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        });
    }

    /**
     * Legacy deposits/withdrawals/transfers/creditor-bill-payments have no dedicated
     * source table — they only ever existed as rows in the generic `transactions` table
     * (type 1/6/4/3). Synthesize the new dedicated entity rows from those, then build
     * the ac_transactions ledger 1:1 from every legacy transaction row.
     */
    protected function migrateDepositsWithdrawalsTransfersAndBills(): void
    {
        LegacyTransaction::orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                if (DB::table('ac_transactions')->where('legacy_id', $row->id)->exists()) {
                    continue;
                }

                match ((int) $row->type) {
                    5 => $this->linkExpenseTransaction($row),
                    7 => $this->linkIouTransaction($row),
                    1 => $this->createDepositTransaction($row),
                    6 => $this->createWithdrawalTransaction($row),
                    4 => $this->createTransferTransaction($row),
                    3 => $this->createCreditorBillPaymentTransaction($row),
                    default => null,
                };
            }
        });
    }

    protected function nextTxnNo(): string
    {
        return SequenceGenerator::next('transaction');
    }

    protected function insertTransaction(array $data): void
    {
        DB::table('ac_transactions')->insert(array_merge([
            'transaction_no' => $this->nextTxnNo(),
            'created_at' => $data['created_at'] ?? now(),
            'updated_at' => $data['updated_at'] ?? now(),
        ], $data));
    }

    protected function statusFor(LegacyTransaction $row): string
    {
        return match (strtolower((string) $row->status)) {
            'success' => 'success',
            'refund' => 'reversed',
            'pending' => 'pending',
            default => 'success',
        };
    }

    protected function linkExpenseTransaction(LegacyTransaction $row): void
    {
        $accountId = $this->accountId($row->account_id);
        $expenseId = DB::table('ac_expenses')->where('legacy_id', $row->src_id)->value('id');
        if (!$accountId || !$expenseId) {
            return;
        }

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'expense',
            'source_id' => $expenseId,
            'account_id' => $accountId,
            'direction' => 'debit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $this->statusFor($row),
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function linkIouTransaction(LegacyTransaction $row): void
    {
        $accountId = $this->accountId($row->account_id);
        $iouId = DB::table('ac_ious')->where('legacy_id', $row->src_id)->value('id');
        if (!$accountId || !$iouId) {
            return;
        }

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'iou',
            'source_id' => $iouId,
            'account_id' => $accountId,
            'direction' => 'debit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $this->statusFor($row),
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function createDepositTransaction(LegacyTransaction $row): void
    {
        $accountId = $this->accountId($row->account_id);
        if (!$accountId) {
            return;
        }

        $depositId = DB::table('ac_deposits')->insertGetId([
            'legacy_id' => $row->id,
            'deposit_no' => SequenceGenerator::next('deposit'),
            'account_id' => $accountId,
            'amount' => $row->amount,
            'received_from' => $row->billing_name,
            'received_method' => $row->payment_method,
            'bank_name' => $row->billing_reason,
            'description' => $row->billing_note,
            'status' => strtolower((string) $row->status) === 'success' ? 'success' : 'pending',
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'editedby_id' => $row->editedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'deposit',
            'source_id' => $depositId,
            'account_id' => $accountId,
            'direction' => 'credit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $this->statusFor($row),
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function createWithdrawalTransaction(LegacyTransaction $row): void
    {
        $accountId = $this->accountId($row->account_id);
        if (!$accountId) {
            return;
        }

        $withdrawalId = DB::table('ac_withdrawals')->insertGetId([
            'legacy_id' => $row->id,
            'withdrawal_no' => SequenceGenerator::next('withdrawal'),
            'account_id' => $accountId,
            'payment_method_id' => $this->paymentMethodId($row->payment_method_id),
            'amount' => $row->amount,
            'bank_name' => $row->billing_reason,
            'description' => $row->billing_note,
            'status' => 'success',
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'editedby_id' => $row->editedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'withdrawal',
            'source_id' => $withdrawalId,
            'account_id' => $accountId,
            'direction' => 'debit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $this->statusFor($row),
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function createTransferTransaction(LegacyTransaction $row): void
    {
        // Legacy quirk (see this session's earlier findings): type=4 transfer rows
        // never had account_id set — the real accounts are src_id (from) and
        // payment_method_id (to).
        $fromAccountId = $this->accountId($row->src_id);
        $toAccountId = $this->accountId($row->payment_method_id);
        if (!$fromAccountId || !$toAccountId) {
            return;
        }

        $transferId = DB::table('ac_balance_transfers')->insertGetId([
            'legacy_id' => $row->id,
            'transfer_no' => SequenceGenerator::next('transfer'),
            'from_account_id' => $fromAccountId,
            'to_account_id' => $toAccountId,
            'amount' => $row->amount,
            'description' => $row->billing_note,
            'status' => 'success',
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'editedby_id' => $row->editedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $status = $this->statusFor($row);

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'transfer',
            'source_id' => $transferId,
            'account_id' => $fromAccountId,
            'direction' => 'debit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $status,
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $this->insertTransaction([
            'legacy_id' => null,
            'source_type' => 'transfer',
            'source_id' => $transferId,
            'account_id' => $toAccountId,
            'direction' => 'credit',
            'amount' => $row->amount,
            'balance_after' => 0,
            'status' => $status,
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function createCreditorBillPaymentTransaction(LegacyTransaction $row): void
    {
        $accountId = $this->accountId($row->account_id);
        if (!$accountId) {
            return;
        }

        $paymentId = DB::table('ac_creditor_bill_payments')->insertGetId([
            'legacy_id' => $row->id,
            'payment_no' => SequenceGenerator::next('creditor_bill_payment'),
            'creditor_id' => $this->creditorId($row->user_id),
            'purchase_id' => $row->src_id,
            'account_id' => $accountId,
            'payment_method_id' => $this->paymentMethodId($row->payment_method_id),
            'amount' => $row->amount,
            'description' => $row->billing_note,
            'status' => 'success',
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'editedby_id' => $row->editedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $this->insertTransaction([
            'legacy_id' => $row->id,
            'source_type' => 'creditor_bill_payment',
            'source_id' => $paymentId,
            'account_id' => $accountId,
            'direction' => 'debit',
            'amount' => $row->amount,
            'balance_after' => $row->balance ?? 0,
            'status' => $this->statusFor($row),
            'transaction_date' => $row->created_at,
            'addedby_id' => $row->addedby_id,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    protected function recomputeBalances(): void
    {
        $accounts = DB::table('ac_accounts')->get();

        foreach ($accounts as $account) {
            $balance = DB::table('ac_transactions')
                ->where('account_id', $account->id)
                ->where('status', 'success')
                ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as balance")
                ->value('balance') ?? 0;

            DB::table('ac_accounts')->where('id', $account->id)->update(['current_balance' => $balance]);
        }
    }

    protected function verify(): void
    {
        $this->info('Verification: legacy Attribute::getCurrentBalance() vs new ac_accounts.current_balance');

        $rows = LegacyAttribute::where('type', 10)->orderBy('id')->get();
        $allMatch = true;

        foreach ($rows as $legacyAccount) {
            $legacyBalance = round((float) $legacyAccount->getCurrentBalance(), 2);
            $newBalance = round((float) (DB::table('ac_accounts')->where('legacy_id', $legacyAccount->id)->value('current_balance') ?? 0), 2);
            $diff = round($newBalance - $legacyBalance, 2);
            $mark = $diff == 0.0 ? 'OK' : 'MISMATCH';

            if ($diff != 0.0) {
                $allMatch = false;
            }

            $this->line(sprintf('  [%s] %-30s legacy=%s new=%s diff=%s', $mark, $legacyAccount->name, $legacyBalance, $newBalance, $diff));
        }

        $this->newLine();
        $this->line('Row counts — legacy vs migrated:');
        $this->line('  expenses: ' . LegacyExpense::count() . ' vs ' . DB::table('ac_expenses')->count());
        $this->line('  ious: ' . LegacyExpenseIou::count() . ' vs ' . DB::table('ac_ious')->count());
        $this->line('  transactions: ' . LegacyTransaction::count() . ' vs ' . DB::table('ac_transactions')->whereNotNull('legacy_id')->count() . ' linked (+transfer credit legs)');

        if ($allMatch) {
            $this->info('✅ All account balances match 100%.');
        } else {
            $this->error('❌ Some account balances do not match — review above before cutover.');
        }
    }
}
