<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'ac_expenses';

    protected $fillable = [
        'expense_no', 'category_id', 'account_id', 'payment_method_id', 'branch_id',
        'amount', 'company_name', 'receiver_name', 'receiver_mobile', 'description',
        'status', 'audit_at', 'audit_by', 'transaction_date', 'addedby_id', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'audit_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'source_id')->where('source_type', 'expense');
    }
}
