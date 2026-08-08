<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTransfer extends Model
{
    protected $table = 'ac_balance_transfers';

    protected $fillable = [
        'transfer_no', 'from_account_id', 'to_account_id', 'amount', 'description',
        'status', 'transaction_date', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
