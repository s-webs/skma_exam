<?php

namespace App\Console\Commands;

use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;

class SyncDefaultRolesCommand extends Command
{
    protected $signature = 'roles:sync-defaults';

    protected $description = 'Create default roles and permissions (idempotent)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);

        $this->info('Default roles and permissions synced.');

        return self::SUCCESS;
    }
}
