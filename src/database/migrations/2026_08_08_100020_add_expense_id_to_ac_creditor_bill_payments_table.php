<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ac_creditor_bill_payments', 'expense_id')) {
            Schema::table('ac_creditor_bill_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('expense_id')->nullable()->after('creditor_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ac_creditor_bill_payments', 'expense_id')) {
            Schema::table('ac_creditor_bill_payments', function (Blueprint $table) {
                $table->dropColumn('expense_id');
            });
        }
    }
};
