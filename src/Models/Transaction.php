<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'ac_transactions';

    protected $fillable = [
        'transaction_no', 'source_type', 'source_id', 'account_id', 'direction',
        'amount', 'balance_after', 'status', 'transaction_date', 'addedby_id', 'editedby_id', 'legacy_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'source_id');
    }

    public function iou()
    {
        return $this->belongsTo(Iou::class, 'source_id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'source_id');
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class, 'source_id');
    }

    public function transfer()
    {
        return $this->belongsTo(BalanceTransfer::class, 'source_id');
    }

    public function creditorBillPayment()
    {
        return $this->belongsTo(CreditorBillPayment::class, 'source_id');
    }

    /** The originating record (Expense/Iou/Deposit/...), resolved by source_type. */
    public function getSourceAttribute()
    {
        return match ($this->source_type) {
            'expense' => $this->expense,
            'iou' => $this->iou,
            'deposit' => $this->deposit,
            'withdrawal' => $this->withdrawal,
            'transfer' => $this->transfer,
            'creditor_bill_payment' => $this->creditorBillPayment,
            default => null,
        };
    }

    /** Payment method name of the originating record, for display. */
    public function getPaymentMethodNameAttribute(): ?string
    {
        $source = $this->source;

        return $source?->paymentMethod?->name;
    }
}
