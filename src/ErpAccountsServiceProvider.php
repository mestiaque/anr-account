<?php

namespace ME\Accounts;

use Illuminate\Support\ServiceProvider;
use ME\Accounts\Console\Commands\MigrateLegacyCommand;
use ME\Accounts\Console\Commands\ReconcileCommand;
use ME\Accounts\Console\Commands\SyncCommand;
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

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'erp-accounts-sidebar');
        }

        if (file_exists(__DIR__ . '/Config/permission.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permission.php', 'erp-accounts-permission');
        }
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'erp-accounts');

        // Merge this package's permission module into the main permission config,
        // same pattern the HR package uses — additive, never touches existing modules.
        $accountsPermissions = config('erp-accounts-permission');
        if ($accountsPermissions && is_array($accountsPermissions)) {
            $mainPermissions = config('permission', []);
            if (isset($mainPermissions['modules']) && is_array($mainPermissions['modules'])) {
                foreach ($accountsPermissions as $moduleKey => $moduleValue) {
                    $mainPermissions['modules'][$moduleKey] = $moduleValue;
                }
                config(['permission' => $mainPermissions]);
            }
        }

        // The only place account-balance math is allowed to happen.
        Expense::observe(ExpenseObserver::class);
        Iou::observe(IouObserver::class);
        Deposit::observe(DepositObserver::class);
        Withdrawal::observe(WithdrawalObserver::class);
        BalanceTransfer::observe(BalanceTransferObserver::class);
        CreditorBillPayment::observe(CreditorBillPaymentObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateLegacyCommand::class,
                ReconcileCommand::class,
                SyncCommand::class,
            ]);
        }
    }
}
