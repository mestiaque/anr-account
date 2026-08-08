<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ac_expenses.category_id has a foreign key to ac_expense_categories.id.
 * category_id=0 is used as a sentinel for the display-only shadow Expense created
 * alongside a creditor bill payment (see CreditorBillPaymentController::store) — this
 * seeds the id=0 row the FK requires.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('ac_expense_categories')->where('id', 0)->exists()) {
            DB::statement("SET SESSION sql_mode = CONCAT(@@sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");

            DB::table('ac_expense_categories')->insert([
                'id' => 0,
                'name' => 'Creditor Bill Payment',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::statement("SET SESSION sql_mode = REPLACE(@@sql_mode, ',NO_AUTO_VALUE_ON_ZERO', '')");
        }
    }

    public function down(): void
    {
        DB::table('ac_expense_categories')->where('id', 0)->delete();
    }
};
