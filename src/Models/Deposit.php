<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'ac_deposits';

    protected $fillable = [
        'deposit_no', 'account_id', 'amount', 'received_from', 'received_method',
        'bank_name', 'description', 'status', 'transaction_date', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'source_id')->where('source_type', 'deposit');
    }
}
