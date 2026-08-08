<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class CreditorBillPayment extends Model
{
    protected $table = 'ac_creditor_bill_payments';

    protected $fillable = [
        'payment_no', 'creditor_id', 'purchase_id', 'account_id', 'payment_method_id',
        'amount', 'description', 'status', 'transaction_date', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function creditor()
    {
        return $this->belongsTo(\App\Models\User::class, 'creditor_id');
    }
}
