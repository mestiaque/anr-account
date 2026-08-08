<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Iou extends Model
{
    protected $table = 'ac_ious';

    protected $fillable = [
        'iou_no', 'employee_id', 'user_id', 'account_id', 'payment_method_id', 'branch_id',
        'amount', 'company_name', 'receiver_name', 'description', 'status',
        'transaction_date', 'addedby_id', 'editedby_id',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employeeUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'source_id')->where('source_type', 'iou');
    }
}
