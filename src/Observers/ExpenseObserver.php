<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class ExpenseObserver
{
    public function creating(Expense $expense): void
    {
        if (!$expense->expense_no) {
            $expense->expense_no = SequenceGenerator::next('expense');
        }
    }

    public function created(Expense $expense): void
    {
        $account = Account::find($expense->account_id);

        LedgerService::record(
            $account,
            'expense',
            $expense->id,
            'debit',
            (float) $expense->amount,
            $expense->transaction_date,
            $expense->addedby_id
        );
    }

    public function updated(Expense $expense): void
    {
        if (!$expense->wasChanged('amount')) {
            return;
        }

        $transaction = Transaction::where('source_type', 'expense')->where('source_id', $expense->id)->first();

        if ($transaction) {
            LedgerService::adjustAmount($transaction, (float) $expense->amount);
        }
    }

    public function deleted(Expense $expense): void
    {
        $transaction = Transaction::where('source_type', 'expense')->where('source_id', $expense->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
