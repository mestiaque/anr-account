<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Iou;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class IouObserver
{
    public function creating(Iou $iou): void
    {
        if (!$iou->iou_no) {
            $iou->iou_no = SequenceGenerator::next('iou');
        }
    }

    public function created(Iou $iou): void
    {
        $account = Account::find($iou->account_id);

        LedgerService::record(
            $account,
            'iou',
            $iou->id,
            'debit',
            (float) $iou->amount,
            $iou->transaction_date,
            $iou->addedby_id
        );
    }

    public function updated(Iou $iou): void
    {
        $transaction = Transaction::where('source_type', 'iou')->where('source_id', $iou->id)->first();

        if (!$transaction) {
            return;
        }

        // Completing an IOU refunds the money back to the account.
        if ($iou->wasChanged('status') && $iou->status === 'completed') {
            LedgerService::reverse($transaction);
            return;
        }

        if ($iou->wasChanged('amount') && $transaction->status === 'success') {
            LedgerService::adjustAmount($transaction, (float) $iou->amount);
        }

        if ($iou->wasChanged('transaction_date')) {
            $transaction->transaction_date = $iou->transaction_date;
            $transaction->save();
        }
    }

    public function deleted(Iou $iou): void
    {
        $transaction = Transaction::where('source_type', 'iou')->where('source_id', $iou->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
