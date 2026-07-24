<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * - المحاسب / أمين الصندوق: invoices.edit (التعديل مقيّد بساعة في RoleNav)
 * - عبدالله (rev_acc_2): دور مساعد المدير ليرى دورة الإيراد كاملة
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';
        Permission::firstOrCreate(['name' => 'invoices.edit', 'guard_name' => $guard]);

        foreach (['accountant', 'cashier'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            if (! $role->hasPermissionTo('invoices.edit')) {
                $role->givePermissionTo('invoices.edit');
            }
        }

        Role::firstOrCreate(['name' => 'assistant_manager', 'guard_name' => $guard]);

        $assistant = User::query()
            ->where('username', 'rev_acc_2')
            ->orWhere('name_ar', 'like', '%عبدالله هزاع العتيبي%')
            ->get();

        foreach ($assistant as $user) {
            $user->syncRoles(['assistant_manager']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['accountant', 'cashier'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role && $role->hasPermissionTo('invoices.edit')) {
                $role->revokePermissionTo('invoices.edit');
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
