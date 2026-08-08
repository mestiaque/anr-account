<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE ac_accounts CHANGE addedby_id created_by BIGINT UNSIGNED NULL');

        Schema::table('ac_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('owner')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ac_accounts', function (Blueprint $table) {
            $table->dropColumn('owner');
        });

        DB::statement('ALTER TABLE ac_accounts CHANGE created_by addedby_id BIGINT UNSIGNED NULL');
    }
};
