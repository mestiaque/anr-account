<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A prior `migrate:rollback --step=1` on 2026_08_08_100012 partially executed its
 * down() (dropping legacy_id from these 7 tables) without the framework recording
 * the rollback, leaving the migrations table out of sync with the actual schema.
 * This re-applies the missing columns idempotently.
 */
return new class extends Migration
{
    protected array $tables = [
        'ac_branches', 'ac_payment_methods', 'ac_expense_categories', 'ac_accounts',
        'ac_expenses', 'ac_ious', 'ac_transactions',
    ];

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
        // No-op: down() of 2026_08_08_100012 already covers dropping these.
    }
};
