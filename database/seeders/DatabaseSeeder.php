<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => 'password',
        ]);

        $this->seedPatientRecords();
        $this->seedStaffRecords();
        $this->seedWardRecords();
        $this->seedClinicalRecords();
    }

    private function seedPatientRecords(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        foreach ([
            [1, 'John', 'Doe', '12 Market St', '09171234567', '1985-06-15', 'Male', 'Married', '2026-01-10'],
            [2, 'Maria', 'Lopez', 'Zone 3 CDO', '09987654321', '1990-03-22', 'Female', 'Single', '2026-02-05'],
            [3, 'Carlos', 'Reyes', 'Lapasan CDO', '09179876543', '1978-11-09', 'Male', 'Married', '2026-03-12'],
            [4, 'Anne', 'Phelps', '44 North Briges Cannomills Edinburgh', '0132-332-4111', '1933-12-12', 'Female', 'Single', '1995-02-21'],
            [5, 'Kian', 'Valdez', 'Balingasag', '09763256548', '1991-09-06', 'Male', 'Married', '2025-12-03'],
            [6, 'Ramon', 'Laforteza', 'Cagayan de Oro City', '09567432345', '1988-10-30', 'Male', 'Married', '2025-11-18'],
            [7, 'Chloe', 'Sanchez', 'Iligan City', '09875674563', '1985-03-03', 'Female', 'Widowed', '2025-11-13'],
            [11, 'James', 'Smith', 'Cagayan de Oro', '09123456789', '2000-01-01', 'Male', 'Single', '2026-05-08'],
            [12, 'Mark', 'Santos', 'Cagayan de Oro', '09111111111', '2001-03-15', 'Male', 'Single', '2026-05-11'],
        ] as [$id, $firstName, $lastName, $address, $phone, $dob, $sex, $maritalStatus, $registered]) {
            DB::table('patients')->updateOrInsert(['patient_id' => $id], [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $address,
                'phone' => $phone,
                'date_of_birth' => $dob,
                'sex' => $sex,
                'marital_status' => $maritalStatus,
                'date_registered' => $registered,
            ]);
        }

        if (Schema::hasTable('next_of_kin')) {
            foreach ([
                [1, 1, 'James Phelps', 'Father', '145 Rowlands Street Paisley', '0141-848-2211'],
                [2, 2, 'Jane Doe', 'Wife', '12 Market St', '09170000000'],
                [3, 3, 'Ana Lopez', 'Mother', 'Zone 3 CDO', '09980000000'],
                [4, 4, 'Luis Reyes', 'Brother', 'Lapasan CDO', '09179999999'],
                [5, 5, 'Jennie Valdez', 'Wife', 'Balingasag', '09234565432'],
                [6, 6, 'Dainty Laforteza', 'Wife', 'Cagayan de Oro', '09876453456'],
                [7, 7, 'Dianne Sanchez', 'Guardian', 'Iligan', '09872345678'],
            ] as [$id, $patientId, $name, $relationship, $address, $phone]) {
                DB::table('next_of_kin')->updateOrInsert(['kin_id' => $id], [
                    'patient_id' => $patientId,
                    'full_name' => $name,
                    'relationship' => $relationship,
                    'address' => $address,
                    'phone' => $phone,
                ]);
            }
        }

        if (Schema::hasTable('local_doctors')) {
            foreach ([
                [1, 'Dr. Helen Pearson', 'CL1001', '22 Cannongate Way Edinburgh', '0131-332-0012'],
                [2, 'Dr. Mark Santos', 'CL1002', 'CDO City', '09171230000'],
                [3, 'Dr. Angela Cruz', 'CL1003', 'CDO City', '09171231111'],
                [4, 'Dr. Helen Pearson', 'CLN-001', '22 Cannongate Way, Edinburgh', '0131-332-0012'],
            ] as [$id, $name, $clinicNumber, $address, $phone]) {
                DB::table('local_doctors')->updateOrInsert(['doctor_id' => $id], [
                    'full_name' => $name,
                    'clinic_number' => $clinicNumber,
                    'address' => $address,
                    'phone' => $phone,
                ]);
            }
        }

        if (Schema::hasTable('patient_referrals')) {
            foreach ([
                [1, 1, 1, '1995-02-20'],
                [2, 2, 2, '2026-01-11'],
                [3, 3, 3, '2026-02-06'],
                [4, 4, 2, '2026-03-13'],
                [5, 5, 1, '2025-12-01'],
                [6, 6, 2, '2025-11-10'],
                [7, 7, 3, '2025-11-14'],
                [17, 1, 1, '2026-05-12'],
                [18, 1, 1, '2026-05-12'],
                [19, 1, 1, '2026-05-12'],
            ] as [$id, $patientId, $doctorId, $date]) {
                DB::table('patient_referrals')->updateOrInsert(['referral_id' => $id], [
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'referral_date' => $date,
                ]);
            }
        }
    }

    private function seedStaffRecords(): void
    {
        if (! Schema::hasTable('staff')) {
            return;
        }

        $staff = [
            ['S011', 'Moira', 'Samuel', '49 School Road Broxburn', '01506-45633', '1961-05-30', 'Female', 'WB123423D', 'Charge Nurse', 18760.00, '1C'],
            ['S012', 'Anna', 'Cruz', 'CDO', '09170001111', '1988-07-12', 'Female', 'AB123', 'Nurse', 15000.00, '1A'],
            ['S013', 'Leo', 'Martinez', 'CDO', '09170002222', '1980-02-20', 'Male', 'CD456', 'Doctor', 30000.00, '2B'],
            ['S014', 'Kent', 'Pasco', 'CDO', '09765476789', '1990-09-16', 'Male', 'EF789', 'Nurse', 15000.00, '1A'],
            ['S015', 'Loisa', 'Nieves', 'CDO', '09872345436', '1995-12-30', 'Female', 'GH123', 'Consultant', 30000.00, '2B'],
            ['S016', 'Edmond', 'Solana', 'CDO', '09871237654', '1997-09-01', 'Male', 'IJ456', 'Medical Officer', 30000.00, '2B'],
        ];

        foreach ($staff as [$id, $firstName, $lastName, $address, $phone, $dob, $sex, $nin, $position, $salary, $scale]) {
            DB::table('staff')->updateOrInsert(['staff_id' => $id], [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $address,
                'phone' => $phone,
                'date_of_birth' => $dob,
                'sex' => $sex,
                'nin' => $nin,
                'staff_position' => $position,
                'salary' => $salary,
                'salary_scale' => $scale,
            ]);
        }

        if (Schema::hasTable('staff_contracts')) {
            foreach ([
                [1, 'S011', 40.00, 'Permanent', 'Monthly'],
                [2, 'S012', 40.00, 'Permanent', 'Monthly'],
                [3, 'S013', 38.00, 'Permanent', 'Monthly'],
                [5, 'S015', 38.00, 'Temporary', 'Monthly'],
                [6, 'S016', 40.00, 'Temporary', 'Monthly'],
            ] as [$id, $staffId, $hours, $contract, $payment]) {
                DB::table('staff_contracts')->updateOrInsert(['contract_id' => $id], [
                    'staff_id' => $staffId,
                    'hours_per_week' => $hours,
                    'contract_type' => $contract,
                    'payment_type' => $payment,
                ]);
            }
        }

        if (Schema::hasTable('work_experience')) {
            foreach ([
                [1, 'S011', 'Senior Resident', 'Jr. Borja General Hospital', '2012-06-01', '2025-12-31'],
                [2, 'S012', 'Staff Nurse', 'Jr. Borja General Hospital', '2014-03-01', '2024-12-15'],
                [3, 'S013', 'Junior Doctor', 'Jr. Borja General Hospital', '2014-01-10', '2025-06-30'],
                [4, 'S014', 'Staff Nurse', 'Cannongate Way Edinburgh', '2014-12-10', '2024-12-15'],
                [5, 'S015', 'Consultant', 'Cannongate Way Edinburgh', '2013-01-16', '2024-04-15'],
                [6, 'S016', 'Medical Officer', 'Cannongate Way Edinburgh', '2012-10-10', '2025-12-31'],
            ] as [$id, $staffId, $position, $organization, $start, $end]) {
                DB::table('work_experience')->updateOrInsert(['experience_id' => $id], [
                    'staff_id' => $staffId,
                    'staff_position' => $position,
                    'organization' => $organization,
                    'start_date' => $start,
                    'end_date' => $end,
                ]);
            }
        }

        if (Schema::hasTable('qualifications')) {
            foreach ([
                [1, 'S011', 'Bachelor of Science in Nursing', 'University of Edinburgh', '2010-06-15'],
                [2, 'S012', 'Doctor of Medicine (MD)', 'Harvard Medical School', '2008-05-20'],
                [3, 'S013', 'Bachelor of Science in Nursing', 'Johns Hopkins University', '2009-03-20'],
                [4, 'S014', 'Bachelor of Science in Nursing', 'University of Edinburgh', '2010-06-15'],
                [5, 'S015', 'Doctor of Medicine (MD)', 'University of Edinburgh', '2010-06-15'],
                [6, 'S016', 'Doctor of Medicine (MD)', 'Harvard Medical School', '2008-06-15'],
            ] as [$id, $staffId, $type, $institution, $date]) {
                DB::table('qualifications')->updateOrInsert(['qualification_id' => $id], [
                    'staff_id' => $staffId,
                    'qualification_type' => $type,
                    'institution' => $institution,
                    'date_obtained' => $date,
                ]);
            }
        }
    }

    private function seedWardRecords(): void
    {
        if (! Schema::hasTable('wards')) {
            return;
        }

        $wards = [
            [1, 'Orthopaedic', 'Block E', 20, '7711'],
            [2, 'Pediatrics', 'Block A', 15, '7712'],
            [3, 'General', 'Block B', 25, '7713'],
            [4, 'Cardiology', 'Block C', 13, '7714'],
            [5, 'Neurology', 'Block C', 13, '7715'],
            [6, 'Oncology', 'Block D', 13, '7716'],
            [7, 'Maternity', 'Block A', 13, '7717'],
            [8, 'Surgical', 'Block E', 13, '7718'],
            [9, 'Emergency', 'Block F', 13, '7719'],
            [10, 'ICU', 'Block F', 13, '7720'],
            [11, 'Geriatric', 'Block G', 13, '7721'],
            [12, 'Psychiatric', 'Block H', 13, '7722'],
            [13, 'Dermatology', 'Block D', 13, '7723'],
            [14, 'ENT', 'Block B', 13, '7724'],
            [15, 'Renal', 'Block G', 13, '7725'],
            [16, 'Respiratory', 'Block H', 13, '7726'],
            [17, 'Out-Patient Recovery', 'Block I', 11, '7727'],
        ];

        $triggersDisabled = false;

        try {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE wards DISABLE TRIGGER USER');
                $triggersDisabled = true;
            }

            foreach ($wards as [$id, $name, $location, $beds, $extension]) {
                $values = [
                    'ward_name' => $name,
                    'location' => $location,
                    'total_beds' => $beds,
                    'tel_extension' => $extension,
                ];

                if (Schema::hasColumn('wards', 'ward_number')) {
                    $values['ward_number'] = $id;
                }

                DB::table('wards')->updateOrInsert(['ward_id' => $id], $values);
            }
        } finally {
            if ($triggersDisabled) {
                DB::statement('ALTER TABLE wards ENABLE TRIGGER USER');
            }
        }

        if (Schema::hasTable('ward_staff')) {
            foreach ([
                [1, 1, 'S011', 'Nurse', 'Early'],
                [2, 2, 'S012', 'Nurse', 'Night'],
                [3, 3, 'S013', 'Doctor', 'Night'],
            ] as [$id, $wardId, $staffId, $role, $shift]) {
                DB::table('ward_staff')->updateOrInsert(['ward_staff_id' => $id], [
                    'ward_id' => $wardId,
                    'staff_id' => $staffId,
                    'role' => $role,
                    'shift' => $shift,
                ]);
            }
        }

        if (Schema::hasTable('beds')) {
            DB::table('beds')->update(['status' => 'Available']);

            $specifiedBeds = [
                1 => [1, 1, 101, 'Occupied'],
                2 => [2, 2, 102, 'Available'],
                3 => [3, 2, 103, 'Occupied'],
                4 => [4, 3, 104, 'Available'],
                5 => [5, 3, 105, 'Available'],
            ];

            foreach ($specifiedBeds as [$id, $wardId, $number, $status]) {
                DB::table('beds')->updateOrInsert(['bed_id' => $id], [
                    'ward_id' => $wardId,
                    'bed_number' => $number,
                    'status' => $status,
                ]);
            }

            $bedId = 6;
            foreach ($wards as [$wardId, , , $totalBeds]) {
                $existingNumbers = collect($specifiedBeds)
                    ->filter(fn ($bed) => $bed[1] === $wardId)
                    ->pluck(2)
                    ->all();
                $created = count($existingNumbers);

                while ($created < $totalBeds) {
                    DB::table('beds')->updateOrInsert(['bed_id' => $bedId], [
                        'ward_id' => $wardId,
                        'bed_number' => 1000 + $bedId,
                        'status' => 'Available',
                    ]);
                    $bedId++;
                    $created++;
                }
            }
        }

        if (Schema::hasTable('admissions')) {
            foreach ([
                [1, 1, 1, 1, '2026-01-12', 5, '2026-04-17', '2026-05-01', '2026-05-08'],
                [2, 2, 2, 2, '2026-01-11', 3, '2026-01-11', '2026-01-14', null],
                [3, 3, 3, 3, '2026-02-06', 4, '2026-02-06', '2026-02-10', null],
                [4, 1, 1, 1, '2026-05-08', 5, '2026-05-08', '2026-05-13', null],
                [5, 1, 1, 1, '2026-05-08', 5, '2026-05-08', '2026-05-13', null],
                [6, 1, 1, 1, '2026-05-11', 5, '2026-05-11', '2026-05-16', null],
                [8, 1, 1, 1, '2026-05-11', 5, '2026-05-11', '2026-05-16', null],
            ] as [$id, $patientId, $wardId, $bedId, $waiting, $stay, $admitted, $expectedLeave, $actualLeave]) {
                DB::table('admissions')->updateOrInsert(['admission_id' => $id], [
                    'patient_id' => $patientId,
                    'ward_id' => $wardId,
                    'bed_id' => $bedId,
                    'date_placed_on_waiting_list' => $waiting,
                    'expected_stay_days' => $stay,
                    'date_admitted' => $admitted,
                    'expected_leave_date' => $expectedLeave,
                    'actual_leave_date' => $actualLeave,
                ]);
            }

            $occupiedBedIds = DB::table('admissions')->whereNull('actual_leave_date')->pluck('bed_id')->unique();
            DB::table('beds')->whereIn('bed_id', $occupiedBedIds)->update(['status' => 'Occupied']);
        }
    }

    private function seedClinicalRecords(): void
    {
        if (Schema::hasTable('appointments')) {
            $appointments = [
                [1, 1, 'S015', '2026-05-24', '09:00:00', 'E252', 'Out-Patient Clinic'],
                [2, 4, 'S015', '2026-05-25', '10:30:00', 'E253', 'Waiting List'],
                [3, 7, 'S013', '2026-05-25', '13:00:00', 'E260', null],
            ];

            foreach ($appointments as [$id, $patientId, $consultantId, $date, $time, $room, $recommendation]) {
                $values = [
                    'patient_id' => $patientId,
                    'consultant_id' => $consultantId,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'room' => $room,
                ];

                if (Schema::hasColumn('appointments', 'recommendation')) {
                    $values['recommendation'] = $recommendation;
                }

                DB::table('appointments')->updateOrInsert(['appointment_id' => $id], $values);
            }
        }

        if (Schema::hasTable('outpatients')) {
            foreach ([[1, 1, 1]] as [$id, $patientId, $appointmentId]) {
                DB::table('outpatients')->updateOrInsert(['outpatient_id' => $id], [
                    'patient_id' => $patientId,
                    'appointment_id' => $appointmentId,
                ]);
            }
        }

        if (Schema::hasTable('drugs')) {
            foreach ([
                [1, 'Morphine', 'Pain relief', '10mg/ml', 'Oral', 120, 20, 2.50],
                [2, 'Amoxicillin', 'Antibiotic', '500mg', 'Oral', 300, 50, 0.40],
                [3, 'Saline', 'Fluid replacement', '500ml', 'Intravenous (IV)', 80, 15, 1.25],
            ] as [$id, $name, $description, $dosage, $method, $stock, $reorder, $cost]) {
                $values = [
                    'name' => $name,
                    'description' => $description,
                    'dosage' => $dosage,
                    'method_of_admin' => $method,
                    'quantity_in_stock' => $stock,
                    'reorder_level' => $reorder,
                    'cost_per_unit' => $cost,
                ];

                if (Schema::hasColumn('drugs', 'drug_number')) {
                    $values['drug_number'] = 'D' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
                }

                DB::table('drugs')->updateOrInsert(['drug_id' => $id], $values);
            }
        }

        if (Schema::hasTable('patient_medication')) {
            foreach ([
                [1, 1, 1, 5, 'Oral', '2026-05-12', '2026-05-30'],
                [2, 2, 2, 3, 'Oral', '2026-05-13', null],
                [3, 3, 3, 2, 'Intravenous (IV)', '2026-05-14', '2026-05-27'],
            ] as [$id, $patientId, $drugId, $units, $method, $start, $end]) {
                $values = [
                    'patient_id' => $patientId,
                    'drug_id' => $drugId,
                    'units_per_day' => $units,
                    'start_date' => $start,
                    'end_date' => $end,
                ];

                if (Schema::hasColumn('patient_medication', 'method_of_admin')) {
                    $values['method_of_admin'] = $method;
                }

                DB::table('patient_medication')->updateOrInsert(['medication_id' => $id], $values);
            }
        }

        if (Schema::hasTable('staff_rotas')) {
            foreach ([
                [1, 'S011', 1, '2026-05-18', 'Early', 'Ward 1'],
                [2, 'S012', 2, '2026-05-18', 'Late', 'Ward 2'],
                [3, 'S013', 3, '2026-05-18', 'Night', 'Ward 3'],
                [4, 'S015', null, '2026-05-18', 'Early', 'Out-Patient Clinic'],
            ] as [$id, $staffId, $wardId, $weekStart, $shift, $area]) {
                DB::table('staff_rotas')->updateOrInsert(['rota_id' => $id], [
                    'staff_id' => $staffId,
                    'ward_id' => $wardId,
                    'week_start_date' => $weekStart,
                    'shift' => $shift,
                    'duty_area' => $area,
                ]);
            }
        }
    }
}
