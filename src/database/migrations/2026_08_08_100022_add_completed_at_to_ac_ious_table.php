<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ac_ious', 'completed_at')) {
            Schema::table('ac_ious', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ac_ious', 'completed_at')) {
            Schema::table('ac_ious', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};
