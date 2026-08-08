<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 10)->unique();
            $table->foreignId('from_account_id')->constrained('ac_accounts');
            $table->foreignId('to_account_id')->constrained('ac_accounts');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('success');
            $table->date('transaction_date');
            $table->unsignedBigInteger('addedby_id')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_balance_transfers');
    }
};
