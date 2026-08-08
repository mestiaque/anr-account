<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no', 10)->unique();
            $table->foreignId('category_id')->nullable()->constrained('ac_expense_categories');
            $table->foreignId('account_id')->constrained('ac_accounts');
            $table->foreignId('payment_method_id')->nullable()->constrained('ac_payment_methods');
            $table->foreignId('branch_id')->nullable()->constrained('ac_branches');
            $table->decimal('amount', 15, 2);
            $table->string('company_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_mobile')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active | inactive
            $table->timestamp('audit_at')->nullable();
            $table->unsignedBigInteger('audit_by')->nullable();
            $table->date('transaction_date');
            $table->unsignedBigInteger('addedby_id')->nullable();
            $table->unsignedBigInteger('editedby_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_expenses');
    }
};
