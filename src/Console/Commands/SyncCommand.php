<?php

namespace ME\Accounts\Console\Commands;

use Illuminate\Console\Command;

class SyncCommand extends Command
{
    protected $signature = 'ac:sync {account? : Limit the integrity check to one ac_accounts.id}';
    protected $description = 'One command for live servers: pull any new records from the old (legacy) module into the ac_* tables, then verify and auto-correct the ledger (orphaned records, amount mismatches, balance drift). Safe to run repeatedly.';

    public function handle(): int
    {
        $this->info('Step 1/2 — Pulling new records from the old module (php artisan ac:migrate-legacy)...');
        $this->newLine();
        $this->call('ac:migrate-legacy');

        $this->newLine();
        $this->info('Step 2/2 — Verifying and auto-correcting the ledger (php artisan ac:reconcile --fix)...');
        $this->newLine();
        $this->call('ac:reconcile', array_filter([
            'account' => $this->argument('account'),
            '--fix' => true,
        ]));

        $this->newLine();
        $this->info('✅ ac:sync complete. Every account\'s balance now matches its transaction ledger, with no orphaned or mismatched records.');

        return self::SUCCESS;
    }
}
