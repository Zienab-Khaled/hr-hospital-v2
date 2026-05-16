<?php

namespace App\Console\Commands;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class SyncRolePermissions extends Command
{
    protected $signature = 'permissions:sync-roles';

    protected $description = 'Re-sync role permissions (sidebar/dashboard access rules) from RolesAndPermissionsSeeder';

    public function handle(): int
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Role permissions synced. Users should log out and back in if menus look stale.');

        return self::SUCCESS;
    }
}
