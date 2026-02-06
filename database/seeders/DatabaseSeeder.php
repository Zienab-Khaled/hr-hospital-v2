<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DepartmentsSeeder::class,
            SettingsSeeder::class,
            AdminSeeder::class,
            InsuranceCompaniesSeeder::class,
            CharityEntitiesSeeder::class,
            EmployeesSeeder::class,
            ServicesSeeder::class,
            PatientsSeeder::class,
            VisitsSeeder::class,
        ]);
    }
}
