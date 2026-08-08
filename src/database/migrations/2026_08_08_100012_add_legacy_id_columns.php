<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'ac_branches', 'ac_payment_methods', 'ac_expense_categories', 'ac_accounts',
        'ac_expenses', 'ac_ious', 'ac_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('legacy_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('legacy_id');
            });
        }
    }
};
