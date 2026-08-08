<?php

namespace ME\Accounts\Observers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\Transaction;
use ME\Accounts\Services\LedgerService;
use ME\Accounts\Services\SequenceGenerator;

class CreditorBillPaymentObserver
{
    public function creating(CreditorBillPayment $payment): void
    {
        if (!$payment->payment_no) {
            $payment->payment_no = SequenceGenerator::next('creditor_bill_payment');
        }
    }

    public function created(CreditorBillPayment $payment): void
    {
        $account = Account::find($payment->account_id);

        LedgerService::record(
            $account,
            'creditor_bill_payment',
            $payment->id,
            'debit',
            (float) $payment->amount,
            $payment->transaction_date,
            $payment->addedby_id
        );
    }

    public function updated(CreditorBillPayment $payment): void
    {
        if (!$payment->wasChanged('amount')) {
            return;
        }

        $transaction = Transaction::where('source_type', 'creditor_bill_payment')->where('source_id', $payment->id)->first();

        if ($transaction) {
            LedgerService::adjustAmount($transaction, (float) $payment->amount);
        }
    }

    public function deleted(CreditorBillPayment $payment): void
    {
        $transaction = Transaction::where('source_type', 'creditor_bill_payment')->where('source_id', $payment->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
