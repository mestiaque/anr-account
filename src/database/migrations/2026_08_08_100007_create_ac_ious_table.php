<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_ious', function (Blueprint $table) {
            $table->id();
            $table->string('iou_no', 10)->unique();
            $table->string('employee_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('account_id')->constrained('ac_accounts');
            $table->foreignId('payment_method_id')->nullable()->constrained('ac_payment_methods');
            $table->foreignId('branch_id')->nullable()->constrained('ac_branches');
            $table->decimal('amount', 15, 2);
            $table->string('company_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending | completed
            $table->date('transaction_date');
            $table->unsignedBigInteger('addedby_id')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_ious');
    }
};
