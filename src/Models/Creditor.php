<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
    protected $table = 'ac_creditors';

    protected $fillable = [
        'name', 'company_name', 'mobile', 'email', 'address', 'status',
        'legacy_user_id', 'created_by', 'editedby_id',
    ];

    public function billPayments()
    {
        return $this->hasMany(CreditorBillPayment::class, 'creditor_id');
    }
}
