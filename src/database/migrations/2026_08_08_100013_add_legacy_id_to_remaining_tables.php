<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = ['ac_deposits', 'ac_withdrawals', 'ac_balance_transfers', 'ac_creditor_bill_payments'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'legacy_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('legacy_id')->nullable()->index();
                });
            }
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
