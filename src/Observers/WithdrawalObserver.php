<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Models\Withdrawal;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class WithdrawalObserver
{
    public function creating(Withdrawal $withdrawal): void
    {
        if (!$withdrawal->withdrawal_no) {
            $withdrawal->withdrawal_no = SequenceGenerator::next('withdrawal');
        }
    }

    public function created(Withdrawal $withdrawal): void
    {
        $account = Account::find($withdrawal->account_id);

        LedgerService::record(
            $account,
            'withdrawal',
            $withdrawal->id,
            'debit',
            (float) $withdrawal->amount,
            $withdrawal->transaction_date,
            $withdrawal->addedby_id
        );
    }

    public function updated(Withdrawal $withdrawal): void
    {
        if (!$withdrawal->wasChanged('amount') && !$withdrawal->wasChanged('transaction_date')) {
            return;
        }

        $transaction = Transaction::where('source_type', 'withdrawal')->where('source_id', $withdrawal->id)->first();

        if (!$transaction) {
            return;
        }

        if ($withdrawal->wasChanged('amount')) {
            LedgerService::adjustAmount($transaction, (float) $withdrawal->amount);
        }

        if ($withdrawal->wasChanged('transaction_date')) {
            $transaction->transaction_date = $withdrawal->transaction_date;
            $transaction->save();
        }
    }

    public function deleted(Withdrawal $withdrawal): void
    {
        $transaction = Transaction::where('source_type', 'withdrawal')->where('source_id', $withdrawal->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
