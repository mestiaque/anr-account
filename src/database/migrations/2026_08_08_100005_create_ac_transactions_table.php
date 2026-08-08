<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 10)->unique();
            $table->string('source_type'); // expense | iou | deposit | withdrawal | transfer | creditor_bill_payment
            $table->unsignedBigInteger('source_id');
            $table->foreignId('account_id')->constrained('ac_accounts');
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('status')->default('success'); // success | pending | reversed
            $table->date('transaction_date');
            $table->unsignedBigInteger('addedby_id')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_transactions');
    }
};
