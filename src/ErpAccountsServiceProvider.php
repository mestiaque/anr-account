<?php

namespace ME\Accounts;

use Illuminate\Support\ServiceProvider;
use ME\Accounts\Models\BalanceTransfer;
use ME\Accounts\Models\CreditorBillPayment;
use ME\Accounts\Models\Deposit;
use ME\Accounts\Models\Expense;
use ME\Accounts\Models\Iou;
use ME\Accounts\Models\Withdrawal;
use ME\Accounts\Observers\BalanceTransferObserver;
use ME\Accounts\Observers\CreditorBillPaymentObserver;
use ME\Accounts\Observers\DepositObserver;
use ME\Accounts\Observers\ExpenseObserver;
use ME\Accounts\Observers\IouObserver;
use ME\Accounts\Observers\WithdrawalObserver;

class ErpAccountsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'erp-accounts');
        }
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // The only place account-balance math is allowed to happen.
        Expense::observe(ExpenseObserver::class);
        Iou::observe(IouObserver::class);
        Deposit::observe(DepositObserver::class);
        Withdrawal::observe(WithdrawalObserver::class);
        BalanceTransfer::observe(BalanceTransferObserver::class);
        CreditorBillPayment::observe(CreditorBillPaymentObserver::class);
    }
}
