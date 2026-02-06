<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitsSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::limit(5)->get();
        $receptionDept = Department::where('code', 'RECEPTION')->first();
        $insuranceDept = Department::where('code', 'INSURANCE')->first();
        $cashDept = Department::where('code', 'CASH')->first();
        $registeredBy = User::whereNotNull('username')->first()?->id;

        $caseTypes = ['clinics', 'emergency', 'clinics', 'emergency', 'clinics'];

        foreach ($patients as $i => $patient) {
            $visitDate = now()->subDays($i + 1);
            Visit::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'visit_date' => $visitDate->format('Y-m-d'),
                ],
                [
                    'case_type' => $caseTypes[$i] ?? 'clinics',
                    'notes' => 'زيارة تجريبية',
                    'transferred_department_id' => $patient->payment_type === 'insurance' ? $insuranceDept?->id : ($patient->payment_type === 'cash' ? $cashDept?->id : $receptionDept?->id),
                    'registered_by' => $registeredBy,
                ]
            );
        }
        // زيارة إضافية لبعض المرضى (زيارة ثانية)
        $extraPatients = Patient::limit(2)->get();
        foreach ($extraPatients as $i => $patient) {
            $visitDate = now()->subDays($i + 10);
            Visit::firstOrCreate(
                [
                    'patient_id' => $patient->id,
                    'visit_date' => $visitDate->format('Y-m-d'),
                ],
                [
                    'case_type' => 'emergency',
                    'notes' => 'زيارة طوارئ',
                    'transferred_department_id' => $receptionDept?->id,
                    'registered_by' => $registeredBy,
                ]
            );
        }
    }
}
