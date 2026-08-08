<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $table = 'ac_expense_categories';

    protected $fillable = ['name', 'description', 'status', 'addedby_id', 'editedby_id', 'legacy_id'];
}
