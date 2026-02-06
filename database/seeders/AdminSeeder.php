<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::firstOrCreate(
            ['code' => 'ADMIN'],
            ['name' => 'Administration', 'name_ar' => 'الإدارة', 'is_active' => true]
        );

        $employee = Employee::firstOrCreate(
            ['department_id' => $dept->id, 'job_title' => 'System Admin'],
            ['name' => 'System Admin', 'name_ar' => 'مدير النظام', 'status' => 'active']
        );

        // Use plain password - User model casts it to 'hashed' automatically
        $user = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Admin',
                'email' => 'admin@hospital.local',
                'password' => 'password',
                'employee_id' => $employee->id,
            ]
        );

        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
