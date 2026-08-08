<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class CreditorBill extends Model
{
    protected $table = 'ac_creditor_bills';

    protected $fillable = [
        'bill_no', 'creditor_id', 'title', 'amount', 'description',
        'transaction_date', 'created_by', 'editedby_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function creditor()
    {
        return $this->belongsTo(Creditor::class, 'creditor_id');
    }
}
