<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\BalanceTransfer;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class BalanceTransferObserver
{
    public function creating(BalanceTransfer $transfer): void
    {
        if (!$transfer->transfer_no) {
            $transfer->transfer_no = SequenceGenerator::next('transfer');
        }
    }

    public function created(BalanceTransfer $transfer): void
    {
        $fromAccount = Account::find($transfer->from_account_id);
        $toAccount = Account::find($transfer->to_account_id);

        LedgerService::record(
            $fromAccount,
            'transfer',
            $transfer->id,
            'debit',
            (float) $transfer->amount,
            $transfer->transaction_date,
            $transfer->addedby_id
        );

        LedgerService::record(
            $toAccount,
            'transfer',
            $transfer->id,
            'credit',
            (float) $transfer->amount,
            $transfer->transaction_date,
            $transfer->addedby_id
        );
    }

    public function deleted(BalanceTransfer $transfer): void
    {
        Transaction::where('source_type', 'transfer')
            ->where('source_id', $transfer->id)
            ->get()
            ->each(fn (Transaction $t) => LedgerService::reverse($t));
    }
}
