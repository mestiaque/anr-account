<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'ac_accounts';

    protected $fillable = [
        'name', 'description', 'opening_balance', 'status', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Account $account) {
            if ($account->current_balance === null) {
                $account->current_balance = $account->opening_balance ?? 0;
            }
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'account_id');
    }

    public function ious()
    {
        return $this->hasMany(Iou::class, 'account_id');
    }
}
