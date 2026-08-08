<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_no', 10)->unique();
            $table->foreignId('account_id')->constrained('ac_accounts');
            $table->decimal('amount', 15, 2);
            $table->string('received_from')->nullable();
            $table->string('received_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending | success
            $table->date('transaction_date');
            $table->unsignedBigInteger('addedby_id')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_deposits');
    }
};
