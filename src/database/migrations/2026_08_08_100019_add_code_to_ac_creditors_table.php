<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ac_creditors', 'code')) {
            Schema::table('ac_creditors', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ac_creditors', 'code')) {
            Schema::table('ac_creditors', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
