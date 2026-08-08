<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Deposit;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class DepositObserver
{
    public function creating(Deposit $deposit): void
    {
        if (!$deposit->deposit_no) {
            $deposit->deposit_no = SequenceGenerator::next('deposit');
        }
    }

    public function created(Deposit $deposit): void
    {
        $account = Account::find($deposit->account_id);

        // Deposits start pending; the ledger row records intent but only affects
        // the balance once approved (see updated()).
        LedgerService::record(
            $account,
            'deposit',
            $deposit->id,
            'credit',
            (float) $deposit->amount,
            $deposit->transaction_date,
            $deposit->addedby_id,
            $deposit->status === 'success' ? 'success' : 'pending'
        );
    }

    public function updated(Deposit $deposit): void
    {
        if (!$deposit->wasChanged('status') || $deposit->status !== 'success') {
            return;
        }

        $transaction = Transaction::where('source_type', 'deposit')->where('source_id', $deposit->id)->first();

        if ($transaction) {
            LedgerService::markSuccess($transaction);
        }
    }

    public function deleted(Deposit $deposit): void
    {
        $transaction = Transaction::where('source_type', 'deposit')->where('source_id', $deposit->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
