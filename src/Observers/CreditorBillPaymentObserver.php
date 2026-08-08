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

    /**
     * When a payment is linked to an Expense (expense_id set), that Expense's own
     * ExpenseObserver is the single source of truth for the balance deduction — this
     * payment row exists only for the creditor's ledger/reporting and must NOT also
     * create a transaction, or the amount gets deducted twice. Only a payment with no
     * linked expense (e.g. created directly, bypassing the expense-linking flow) gets
     * its own ledger entry here.
     */
    public function created(CreditorBillPayment $payment): void
    {
        if ($payment->expense_id) {
            return;
        }

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
        if ($payment->expense_id || !$payment->wasChanged('amount')) {
            return;
        }

        $transaction = Transaction::where('source_type', 'creditor_bill_payment')->where('source_id', $payment->id)->first();

        if ($transaction) {
            LedgerService::adjustAmount($transaction, (float) $payment->amount);
        }
    }

    public function deleted(CreditorBillPayment $payment): void
    {
        if ($payment->expense_id) {
            return;
        }

        $transaction = Transaction::where('source_type', 'creditor_bill_payment')->where('source_id', $payment->id)->first();

        if ($transaction) {
            LedgerService::reverse($transaction);
        }
    }
}
