<?php

use Illuminate\Support\Facades\Route;
use ME\Accounts\Http\Controllers\AccountController;
use ME\Accounts\Http\Controllers\BalanceTransferController;
use ME\Accounts\Http\Controllers\CreditorBillPaymentController;
use ME\Accounts\Http\Controllers\DepositController;
use ME\Accounts\Http\Controllers\ExpenseCategoryController;
use ME\Accounts\Http\Controllers\ExpenseController;
use ME\Accounts\Http\Controllers\IouController;
use ME\Accounts\Http\Controllers\PaymentMethodController;
use ME\Accounts\Http\Controllers\WithdrawalController;

// Fully independent route set — no Route::any, one dedicated route per action.
// Mirrors the host app's admin/* URL + 'admin.' route-name convention so
// existing blade views only need their route() targets repointed, nothing else.
Route::middleware(['web', 'logUserActivity', 'auth', 'redirectUser'])
    ->prefix('admin/v2')
    ->name('admin.')
    ->group(function () {

        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::post('/', [AccountController::class, 'store'])->name('store');
            Route::get('/{account}', [AccountController::class, 'show'])->name('show');
            Route::put('/{account}', [AccountController::class, 'update'])->name('update');
            Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
            Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('expense-categories')->name('expenseCategories.')->group(function () {
            Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
            Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
            Route::put('/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('update');
            Route::delete('/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('ious')->name('ious.')->group(function () {
            Route::get('/', [IouController::class, 'index'])->name('index');
            Route::get('/completed', [IouController::class, 'completed'])->name('completed');
            Route::post('/', [IouController::class, 'store'])->name('store');
            Route::put('/{iou}', [IouController::class, 'update'])->name('update');
            Route::delete('/{iou}', [IouController::class, 'destroy'])->name('destroy');
            Route::get('/search-employee', [IouController::class, 'searchEmployee'])->name('searchEmployee');
        });

        Route::prefix('deposits')->name('deposits.')->group(function () {
            Route::get('/', [DepositController::class, 'index'])->name('index');
            Route::post('/', [DepositController::class, 'store'])->name('store');
            Route::put('/{deposit}', [DepositController::class, 'update'])->name('update');
            Route::post('/{deposit}/approve', [DepositController::class, 'approve'])->name('approve');
            Route::delete('/{deposit}', [DepositController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [WithdrawalController::class, 'index'])->name('index');
            Route::post('/', [WithdrawalController::class, 'store'])->name('store');
            Route::put('/{withdrawal}', [WithdrawalController::class, 'update'])->name('update');
            Route::delete('/{withdrawal}', [WithdrawalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('balance-transfers')->name('balanceTransfers.')->group(function () {
            Route::get('/', [BalanceTransferController::class, 'index'])->name('index');
            Route::post('/', [BalanceTransferController::class, 'store'])->name('store');
        });

        Route::prefix('creditor-bill-payments')->name('creditorBillPayments.')->group(function () {
            Route::get('/', [CreditorBillPaymentController::class, 'index'])->name('index');
            Route::post('/', [CreditorBillPaymentController::class, 'store'])->name('store');
        });

        Route::prefix('payment-methods')->name('paymentMethods.')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
            Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
            Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
            Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
        });
    });
