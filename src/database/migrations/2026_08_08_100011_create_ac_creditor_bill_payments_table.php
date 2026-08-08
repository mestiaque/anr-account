<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_creditor_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 10)->unique();
            $table->unsignedBigInteger('creditor_id')->nullable(); // FK to legacy users table (suppliers)
            $table->unsignedBigInteger('purchase_id')->nullable(); // FK to legacy purchases/orders table
            $table->foreignId('account_id')->constrained('ac_accounts');
            $table->foreignId('payment_method_id')->nullable()->constrained('ac_payment_methods');
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
        Schema::dropIfExists('ac_creditor_bill_payments');
    }
};
