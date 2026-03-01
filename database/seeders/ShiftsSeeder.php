<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'Shift 1 (12-8)', 'name_ar' => 'شيفت ١ (12-8)', 'start_time' => '00:00', 'end_time' => '08:00', 'sort_order' => 1],
            ['name' => 'Shift 2 (8-4)', 'name_ar' => 'شيفت ٢ (8-4)', 'start_time' => '08:00', 'end_time' => '16:00', 'sort_order' => 2],
            ['name' => 'Shift 3 (4-12)', 'name_ar' => 'شيفت ٣ (4-12)', 'start_time' => '16:00', 'end_time' => '23:59', 'sort_order' => 3],
        ];

        foreach ($shifts as $s) {
            Shift::updateOrCreate(
                ['sort_order' => $s['sort_order']],
                array_merge($s, ['is_active' => true])
            );
        }
    }
}
