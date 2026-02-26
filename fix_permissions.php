<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

try {
    $guards = ['web', 'api'];
    foreach ($guards as $guard) {
        echo "Processing guard: $guard\n";

        // Create Manager Role
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
        echo " - Manager role ensured.\n";

        // Create missing permissions
        $perms = ['visits.view', 'visits.create', 'visits.edit', 'visits.delete'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => $guard]);
            echo " - Permission '$p' ensured.\n";
        }

        // Assign all perms to Admin and Manager
        $allPerms = Permission::where('guard_name', $guard)->get();
        Role::findByName('admin', $guard)->syncPermissions($allPerms);
        Role::findByName('manager', $guard)->syncPermissions($allPerms);
        echo " - All permissions synced to admin and manager.\n";
    }
    echo "\n✅ Success: Roles and Permissions updated successfully!\n";
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
