<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'ac_payment_methods';

    protected $fillable = ['name', 'description', 'status', 'addedby_id', 'editedby_id'];
}
