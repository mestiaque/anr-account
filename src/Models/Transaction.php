<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'ac_transactions';

    protected $fillable = [
        'transaction_no', 'source_type', 'source_id', 'account_id', 'direction',
        'amount', 'balance_after', 'status', 'transaction_date', 'addedby_id', 'editedby_id',
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
}
