<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $table = 'ac_withdrawals';

    protected $fillable = [
        'withdrawal_no', 'account_id', 'payment_method_id', 'amount', 'bank_name',
        'description', 'status', 'transaction_date', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'source_id')->where('source_type', 'withdrawal');
    }
}
