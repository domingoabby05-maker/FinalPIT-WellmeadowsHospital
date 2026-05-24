<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $patients = [
        [
            'patient_id' => 'P10034',
            'first_name' => 'Ronald',
            'last_name' => 'MacDonald',
            'address' => 'Cagayan de Oro',
            'phone' => null,
            'date_of_birth' => '2008-01-01',
            'sex' => 'Male',
            'marital_status' => null,
            'date_registered' => '2026-01-10',
        ],
        [
            'patient_id' => 'P10234',
            'first_name' => 'Anne',
            'last_name' => 'Phelps',
            'address' => '44 North Bridges, Cannonmills, Edinburgh, EH1 5GH',
            'phone' => '0131-332-4111',
            'date_of_birth' => '1933-12-12',
            'sex' => 'Female',
            'marital_status' => 'Single',
            'date_registered' => '1995-02-21',
        ],
        [
            'patient_id' => 'P10451',
            'first_name' => 'Robber',
            'last_name' => 'Drumtree',
            'address' => null,
            'phone' => null,
            'date_of_birth' => null,
            'sex' => null,
            'marital_status' => null,
            'date_registered' => '2026-03-12',
        ],
        [
            'patient_id' => 'P10480',
            'first_name' => 'Steven',
            'last_name' => 'Parks',
            'address' => null,
            'phone' => null,
            'date_of_birth' => null,
            'sex' => null,
            'marital_status' => null,
            'date_registered' => '2026-03-12',
        ],
        [
            'patient_id' => 'P10563',
            'first_name' => 'David',
            'last_name' => 'Black',
            'address' => null,
            'phone' => null,
            'date_of_birth' => null,
            'sex' => null,
            'marital_status' => null,
            'date_registered' => '2026-03-12',
        ],
        [
            'patient_id' => 'P10604',
            'first_name' => 'Ian',
            'last_name' => 'Thompson',
            'address' => null,
            'phone' => null,
            'date_of_birth' => null,
            'sex' => null,
            'marital_status' => null,
            'date_registered' => '2026-03-12',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        $this->relaxNullablePatientColumns();

        $patientIds = collect($this->patients)->pluck('patient_id')->all();

        DB::transaction(function () use ($patientIds) {
            $this->deleteNonCaseStudyPatients($patientIds);

            foreach ($this->patients as $patient) {
                DB::table('patients')->updateOrInsert(
                    ['patient_id' => $patient['patient_id']],
                    $this->onlyExistingColumns('patients', $patient)
                );
            }

            $this->syncAnneDetails();
            $this->syncAdmissions();
        });
    }

    private function relaxNullablePatientColumns(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (['address', 'date_of_birth', 'sex', 'marital_status', 'phone'] as $column) {
            if (Schema::hasColumn('patients', $column)) {
                DB::statement("alter table patients alter column {$column} drop not null");
            }
        }
    }

    private function deleteNonCaseStudyPatients(array $patientIds): void
    {
        $oldPatientIds = DB::table('patients')
            ->whereNotIn('patient_id', $patientIds)
            ->pluck('patient_id')
            ->all();

        if ($oldPatientIds === []) {
            return;
        }

        foreach (['patient_medication', 'outpatients', 'admissions', 'patient_referrals', 'next_of_kin'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'patient_id')) {
                DB::table($table)->whereIn('patient_id', $oldPatientIds)->delete();
            }
        }

        DB::table('patients')->whereIn('patient_id', $oldPatientIds)->delete();
    }

    private function syncAnneDetails(): void
    {
        if (Schema::hasTable('next_of_kin')) {
            DB::table('next_of_kin')->where('patient_id', 'P10234')->delete();
            DB::table('next_of_kin')->insert($this->withStringKey('next_of_kin', 'kin_id', 'K10234', [
                'patient_id' => 'P10234',
                'full_name' => 'James Phelps',
                'relationship' => 'Father',
                'address' => 'Glasgow',
                'phone' => '0141-848-2211',
            ]));
        }

        if (! Schema::hasTable('local_doctors') || ! Schema::hasTable('patient_referrals')) {
            return;
        }

        DB::table('local_doctors')->updateOrInsert(
            ['clinic_number' => 'CL001'],
            $this->onlyExistingColumns('local_doctors', [
                'full_name' => 'Dr. Helen Pearson',
                'clinic_number' => 'CL001',
                'address' => 'Edinburgh Clinic',
                'phone' => null,
            ])
        );

        $doctorId = DB::table('local_doctors')->where('clinic_number', 'CL001')->value('doctor_id');

        DB::table('patient_referrals')->where('patient_id', 'P10234')->delete();
        DB::table('patient_referrals')->insert($this->withStringKey('patient_referrals', 'referral_id', 'R10234', [
            'patient_id' => 'P10234',
            'doctor_id' => $doctorId,
            'referral_date' => '1995-02-21',
        ]));
    }

    private function syncAdmissions(): void
    {
        if (! Schema::hasTable('admissions')) {
            return;
        }

        $admissions = [
            ['admission_id' => 'A10451', 'patient_id' => 'P10451', 'ward_id' => 'W11', 'bed_id' => 'B84'],
            ['admission_id' => 'A10480', 'patient_id' => 'P10480', 'ward_id' => 'W11', 'bed_id' => 'B79'],
            ['admission_id' => 'A10563', 'patient_id' => 'P10563', 'ward_id' => 'W11', 'bed_id' => 'B80'],
            ['admission_id' => 'A10604', 'patient_id' => 'P10604', 'ward_id' => 'W11', 'bed_id' => 'B87'],
        ];

        foreach ($admissions as $admission) {
            DB::table('admissions')->updateOrInsert(
                ['admission_id' => $admission['admission_id']],
                $this->onlyExistingColumns('admissions', $admission + [
                    'date_placed_on_waiting_list' => '2026-03-12',
                    'expected_stay_days' => null,
                    'date_admitted' => '2026-05-08',
                    'expected_leave_date' => null,
                    'actual_leave_date' => null,
                ])
            );
        }
    }

    private function withStringKey(string $table, string $column, string $value, array $row): array
    {
        if (Schema::hasColumn($table, $column) && $this->isStringColumn($table, $column)) {
            $row[$column] = $value;
        }

        return $this->onlyExistingColumns($table, $row);
    }

    private function onlyExistingColumns(string $table, array $row): array
    {
        return collect($row)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    private function isStringColumn(string $table, string $column): bool
    {
        if (DB::getDriverName() === 'pgsql') {
            $result = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                [$table, $column]
            );

            return str_contains((string) ($result->data_type ?? ''), 'char');
        }

        return str_contains((string) Schema::getColumnType($table, $column), 'char');
    }

    public function down(): void
    {
        //
    }
};
