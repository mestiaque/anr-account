<?php

namespace ME\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'ac_branches';

    protected $fillable = ['name', 'bn_name', 'status', 'addedby_id', 'editedby_id'];
}
