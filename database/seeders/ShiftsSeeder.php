<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Night', 'name_ar' => 'ليل', 'start_time' => '00:00', 'end_time' => '08:00', 'sort_order' => 1],
            ['name' => 'Morning', 'name_ar' => 'صباح', 'start_time' => '08:00', 'end_time' => '16:00', 'sort_order' => 2],
            ['name' => 'Afternoon', 'name_ar' => 'عصر', 'start_time' => '16:00', 'end_time' => '23:59', 'sort_order' => 3],
        ];

        foreach ($shifts as $s) {
            Shift::updateOrCreate(
                ['sort_order' => $s['sort_order']],
                array_merge($s, ['is_active' => true])
            );
        }
    }
}
