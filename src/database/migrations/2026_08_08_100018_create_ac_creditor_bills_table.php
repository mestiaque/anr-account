<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_creditor_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no', 10)->unique();
            $table->foreignId('creditor_id')->constrained('ac_creditors');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_creditor_bills');
    }
};
