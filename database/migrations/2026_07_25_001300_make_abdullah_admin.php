<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** عبدالله (rev_acc_2) → دور أدمن كامل */
return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $users = User::query()
            ->where('username', 'rev_acc_2')
            ->orWhere('name_ar', 'like', '%عبدالله هزاع العتيبي%')
            ->get();

        foreach ($users as $user) {
            $user->syncRoles(['admin']);
            $user->forceFill([
                'job_title' => 'Admin',
                'job_title_ar' => 'أدمن',
            ])->save();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'assistant_manager', 'guard_name' => 'web']);

        $users = User::query()
            ->where('username', 'rev_acc_2')
            ->orWhere('name_ar', 'like', '%عبدالله هزاع العتيبي%')
            ->get();

        foreach ($users as $user) {
            $user->syncRoles(['assistant_manager']);
            $user->forceFill([
                'job_title' => 'Assistant Manager',
                'job_title_ar' => 'مساعد المدير',
            ])->save();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
