<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Creditor extends Model
{
    use SoftDeletes;

    protected $table = 'ac_creditors';

    protected $fillable = [
        'name', 'code', 'company_name', 'mobile', 'email', 'address', 'status',
        'legacy_user_id', 'created_by', 'editedby_id', 'deleted_by',
    ];

    public function billPayments()
    {
        return $this->hasMany(CreditorBillPayment::class, 'creditor_id');
    }

    public function bills()
    {
        return $this->hasMany(CreditorBill::class, 'creditor_id');
    }
}
