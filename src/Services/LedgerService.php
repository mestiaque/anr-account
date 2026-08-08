<?php

namespace ME\Accounts\Services;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Transaction;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public static function record(Account $account, string $sourceType, int $sourceId, string $direction, float $amount, $transactionDate, ?int $userId = null, string $status = 'success'): Transaction
    {
        return DB::transaction(function () use ($account, $sourceType, $sourceId, $direction, $amount, $transactionDate, $userId, $status) {
            $account = Account::whereKey($account->id)->lockForUpdate()->first();

            if ($status === 'success') {
                $account->current_balance = $direction === 'credit'
                    ? $account->current_balance + $amount
                    : $account->current_balance - $amount;
                $account->save();
            }

            return Transaction::create([
                'transaction_no' => SequenceGenerator::next('transaction'),
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'account_id' => $account->id,
                'direction' => $direction,
                'amount' => $amount,
                'balance_after' => $account->current_balance,
                'status' => $status,
                'transaction_date' => $transactionDate,
                'addedby_id' => $userId,
            ]);
        });
    }

    /**
     * Adjust an existing (unreversed) transaction's amount, applying only the delta to the account balance.
     */
    public static function adjustAmount(Transaction $transaction, float $newAmount): Transaction
    {
        return DB::transaction(function () use ($transaction, $newAmount) {
            $account = Account::whereKey($transaction->account_id)->lockForUpdate()->first();
            $diff = $newAmount - (float) $transaction->amount;

            if ($transaction->status === 'success' && $diff != 0) {
                $account->current_balance = $transaction->direction === 'credit'
                    ? $account->current_balance + $diff
                    : $account->current_balance - $diff;
                $account->save();
            }

            $transaction->amount = $newAmount;
            $transaction->balance_after = $account->current_balance;
            $transaction->save();

            return $transaction;
        });
    }

    /**
     * Reverse a transaction's effect on the account balance (used for deletes / IOU completion / deposit rejection).
     */
    public static function reverse(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status !== 'success') {
                return $transaction;
            }

            $account = Account::whereKey($transaction->account_id)->lockForUpdate()->first();
            $account->current_balance = $transaction->direction === 'credit'
                ? $account->current_balance - $transaction->amount
                : $account->current_balance + $transaction->amount;
            $account->save();

            $transaction->status = 'reversed';
            $transaction->balance_after = $account->current_balance;
            $transaction->save();

            return $transaction;
        });
    }

    /**
     * Transition a pending transaction to success, applying its balance effect.
     */
    public static function markSuccess(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'success') {
                return $transaction;
            }

            $account = Account::whereKey($transaction->account_id)->lockForUpdate()->first();
            $account->current_balance = $transaction->direction === 'credit'
                ? $account->current_balance + $transaction->amount
                : $account->current_balance - $transaction->amount;
            $account->save();

            $transaction->status = 'success';
            $transaction->balance_after = $account->current_balance;
            $transaction->save();

            return $transaction;
        });
    }
}
