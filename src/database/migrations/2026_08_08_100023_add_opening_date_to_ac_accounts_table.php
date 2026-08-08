<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts previously had no dedicated "opening date" — the edit form displayed
 * and overwrote the real created_at timestamp instead (AccountController::update).
 * This adds a proper opening_date column and backfills it from created_at so no
 * data is lost, and stops the controller from touching the audit timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ac_accounts', 'opening_date')) {
            Schema::table('ac_accounts', function (Blueprint $table) {
                $table->date('opening_date')->nullable()->after('opening_balance');
            });

            DB::statement('UPDATE ac_accounts SET opening_date = DATE(created_at) WHERE opening_date IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ac_accounts', 'opening_date')) {
            Schema::table('ac_accounts', function (Blueprint $table) {
                $table->dropColumn('opening_date');
            });
        }
    }
};
