<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║                                                          ║\n";
        echo "║       🏥 Hospital Management System - Data Seeding       ║\n";
        echo "║                                                          ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        $this->call([
            SettingsSeeder::class,
            DepartmentsSeeder::class,
            ShiftsSeeder::class,
            // ServicesSeeder::class,
            SeedServicesFromXlsSeeder::class,
            InsuranceCompaniesSeeder::class,
            CharityEntitiesSeeder::class,
            RolesAndPermissionsSeeder::class,
            // UsersSeeder::class,
            ProductionUsersSeeder::class,
            RevenueStaffSeeder::class,
            // PatientsSeeder::class,
        ]);

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║                                                          ║\n";
        echo "║              ✅ Database Seeding Complete!               ║\n";
        echo "║                                                          ║\n";
        echo "║  The system is now ready with:                          ║\n";
        echo "║  • Hospital Settings & Manager Info                     ║\n";
        echo "║  • 18 Departments                                       ║\n";
        echo "║  • 35+ Services with Kingdom Codes                      ║\n";
        echo "║  • 6 Insurance Companies with Emails                    ║\n";
        echo "║  • 6 Charity Entities with Emails                       ║\n";
        echo "║  • Users + Revenue Staff (محصلين، تأمين، محاسب، أمين صندوق، مديونيات) ║\n";
        echo "║  • 20 Sample Patients                                   ║\n";
        echo "║                                                          ║\n";
        echo "║  Start by logging in at: /login                         ║\n";
        echo "║                                                          ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
}
