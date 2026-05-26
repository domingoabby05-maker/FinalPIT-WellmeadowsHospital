<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

class HospitalController extends Controller
{
    private const PERMISSIONS = [
        'patients' => ['Medical Director', 'Doctor', 'Consultant', 'Charge Nurse', 'Nurse'],
        'staff' => ['Medical Director', 'Personnel Officer'],
        'admissions' => ['Medical Director', 'Charge Nurse'],
        'appointments' => ['Medical Director', 'Doctor', 'Consultant', 'Charge Nurse'],
        'medications' => ['Medical Director', 'Doctor', 'Consultant', 'Charge Nurse', 'Nurse'],
    ];

    public function index()
    {
        $data = $this->hospitalData();

        return view('hospital.dashboard', $data);
    }

    public function dashboardData()
    {
        $data = $this->hospitalData();

        return response()->json([
            'generated_at' => now()->format('M j, Y g:i A'),
            'stats' => $data['stats'],
            'wards' => $data['wards']->map(fn ($ward) => [
                'ward_id' => $ward->ward_id,
                'occupied_beds' => (int) ($ward->occupied_beds ?? 0),
                'total_beds' => (int) $ward->total_beds,
                'available_beds' => max(((int) $ward->total_beds) - ((int) ($ward->occupied_beds ?? 0)), 0),
                'staff_count' => (int) ($ward->staff_count ?? 0),
            ])->values(),
        ]);
    }

    protected function moduleRedirect(string $module, string $status)
    {
        return redirect()->route('hospital.index')->withFragment($module)->with('status', $status);
    }

    protected function validatePatient(Request $request): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'date_of_birth' => ['required', 'date'],
            'sex' => ['required', 'in:Male,Female'],
            'marital_status' => ['nullable', 'string', 'max:20'],
            'kin_full_name' => ['nullable', 'string', 'max:100'],
            'kin_relationship' => ['nullable', 'string', 'max:50'],
            'kin_address' => ['nullable', 'string'],
            'kin_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'doctor_full_name' => ['nullable', 'string', 'max:100'],
            'clinic_number' => ['nullable', 'string', 'max:50'],
            'doctor_address' => ['nullable', 'string'],
            'doctor_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'referral_date' => ['nullable', 'date'],
        ]);

        return [
            'patient' => collect($validated)->only([
                'first_name',
                'last_name',
                'address',
                'phone',
                'date_of_birth',
                'sex',
                'marital_status',
            ])->all(),
            'kin' => [
                'full_name' => $validated['kin_full_name'] ?? null,
                'relationship' => $validated['kin_relationship'] ?? null,
                'address' => $validated['kin_address'] ?? null,
                'phone' => $validated['kin_phone'] ?? null,
            ],
            'doctor' => [
                'full_name' => $validated['doctor_full_name'] ?? null,
                'clinic_number' => $validated['clinic_number'] ?? null,
                'address' => $validated['doctor_address'] ?? null,
                'phone' => $validated['doctor_phone'] ?? null,
            ],
            'referral_date' => $validated['referral_date'] ?? null,
        ];
    }

    protected function savePatientDetails(string|int $patientId, array $validated, bool $replace = false): void
    {
        if ($replace) {
            if (Schema::hasTable('next_of_kin')) {
                DB::table('next_of_kin')->where('patient_id', $patientId)->delete();
            }

            if (Schema::hasTable('patient_referrals')) {
                DB::table('patient_referrals')->where('patient_id', $patientId)->delete();
            }
        }

        if (Schema::hasTable('next_of_kin') && $this->hasRequiredValues($validated['kin'], ['full_name', 'relationship', 'address'])) {
            DB::table('next_of_kin')->insert(
                $validated['kin']
                + ['patient_id' => $patientId]
                + $this->keyForInsert('next_of_kin', 'kin_id', 'K', 5, (string) $patientId)
            );
        }

        if (
            Schema::hasTable('local_doctors')
            && Schema::hasTable('patient_referrals')
            && $this->hasRequiredValues($validated['doctor'], ['full_name', 'clinic_number', 'address'])
        ) {
            DB::table('local_doctors')->updateOrInsert(
                ['clinic_number' => $validated['doctor']['clinic_number']],
                $validated['doctor']
            );

            $doctorId = DB::table('local_doctors')
                ->where('clinic_number', $validated['doctor']['clinic_number'])
                ->value('doctor_id');

            DB::table('patient_referrals')->insert([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'referral_date' => $validated['referral_date'] ?? now()->toDateString(),
            ] + $this->keyForInsert('patient_referrals', 'referral_id', 'R', 5, (string) $patientId));
        }
    }

    protected function validateAdmission(Request $request): array
    {
        return $request->validate([
            'patient_id' => ['required', 'exists:patients,patient_id'],
            'ward_id' => ['required', 'exists:wards,ward_id'],
            'bed_id' => ['nullable', 'exists:beds,bed_id'],
            'date_placed_on_waiting_list' => ['nullable', 'date'],
            'expected_stay_days' => ['nullable', 'integer', 'min:1'],
            'date_admitted' => ['nullable', 'date'],
            'expected_leave_date' => ['nullable', 'date'],
            'actual_leave_date' => ['nullable', 'date'],
        ]);
    }

    protected function syncBedStatus(string|int $bedId): void
    {
        if (! Schema::hasTable('beds') || ! Schema::hasTable('admissions')) {
            return;
        }

        $occupied = DB::table('admissions')
            ->where('bed_id', $bedId)
            ->whereNull('actual_leave_date')
            ->exists();

        DB::table('beds')->where('bed_id', $bedId)->update([
            'status' => $occupied ? 'Occupied' : 'Available',
        ]);
    }

    protected function validateStaff(Request $request, string|int|null $staffId = null): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'date_of_birth' => ['required', 'date'],
            'sex' => ['required', 'in:Male,Female'],
            'nin' => ['required', 'string', 'max:20', Rule::unique('staff', 'nin')->ignore($staffId, 'staff_id')],
            'position' => ['required', 'string', 'max:50'],
            'salary' => ['required', 'numeric', 'min:0'],
            'salary_scale' => ['nullable', 'string', 'max:20'],
            'hours_per_week' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'contract_type' => ['nullable', Rule::in(['Permanent', 'Temporary'])],
            'payment_type' => ['nullable', 'string', 'max:20'],
            'qualification_type' => ['nullable', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:100'],
            'date_obtained' => ['nullable', 'date'],
            'ward_id' => ['nullable', 'exists:wards,ward_id'],
            'ward_role' => ['nullable', 'string', 'max:100'],
            'shift' => ['nullable', Rule::in(['Early', 'Late', 'Night'])],
            'experience_position' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        return [
            'staff' => collect($validated)->only([
                'first_name',
                'last_name',
                'address',
                'phone',
                'date_of_birth',
                'sex',
                'nin',
                'position',
                'salary',
                'salary_scale',
            ])->all(),
            'contract' => collect($validated)->only(['hours_per_week', 'contract_type', 'payment_type'])->all(),
            'qualification' => collect($validated)->only(['qualification_type', 'institution', 'date_obtained'])->all(),
            'ward_staff' => [
                'ward_id' => $validated['ward_id'] ?? null,
                'role' => $validated['ward_role'] ?? ($validated['position'] ?? null),
                'shift' => $validated['shift'] ?? (filled($validated['ward_id'] ?? null) ? 'Early' : null),
            ],
            'experience' => [
                'staff_position' => $validated['experience_position'] ?? null,
                'position' => $validated['experience_position'] ?? null,
                'organization' => $validated['organization'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ],
        ];
    }

    protected function saveStaffDetails(string|int $staffId, array $validated, bool $replace = false): void
    {
        if ($replace) {
            foreach (['staff_contracts', 'qualifications', 'work_experience', 'ward_staff'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('staff_id', $staffId)->delete();
                }
            }
        }

        if (Schema::hasTable('ward_staff') && $this->hasRequiredValues($validated['ward_staff'], ['ward_id', 'role', 'shift'])) {
            DB::table('ward_staff')->insert(
                $validated['ward_staff']
                + ['staff_id' => $staffId]
                + $this->keyForInsert('ward_staff', 'ward_staff_id', 'WS')
            );
        }

        if (Schema::hasTable('staff_contracts') && $this->hasRequiredValues($validated['contract'], ['hours_per_week', 'contract_type', 'payment_type'])) {
            DB::table('staff_contracts')->insert($validated['contract'] + ['staff_id' => $staffId] + $this->keyForInsert('staff_contracts', 'contract_id', 'C'));
        }

        if (Schema::hasTable('qualifications') && $this->hasRequiredValues($validated['qualification'], ['qualification_type', 'institution', 'date_obtained'])) {
            DB::table('qualifications')->insert($validated['qualification'] + ['staff_id' => $staffId] + $this->keyForInsert('qualifications', 'qualification_id', 'Q'));
        }

        if (Schema::hasTable('work_experience') && $this->hasRequiredValues($validated['experience'], ['position', 'organization', 'start_date'])) {
            $experience = $validated['experience'];
            if (! Schema::hasColumn('work_experience', 'position')) {
                unset($experience['position']);
            }

            DB::table('work_experience')->insert($experience + ['staff_id' => $staffId] + $this->keyForInsert('work_experience', 'experience_id', 'E'));
        }
    }

    protected function hasRequiredValues(array $values, array $keys): bool
    {
        foreach ($keys as $key) {
            if (! filled($values[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function usesStringKey(string $table, string $column): bool
    {
        if (! Schema::hasColumn($table, $column)) {
            return false;
        }

        if (DB::getDriverName() === 'pgsql') {
            $result = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                [$table, $column]
            );

            return str_contains((string) ($result->data_type ?? ''), 'char');
        }

        return str_contains((string) Schema::getColumnType($table, $column), 'char');
    }

    protected function nextStaffNumber(): string
    {
        $lastStaffId = DB::table('staff')
            ->where('staff_id', 'like', 'S%')
            ->orderByDesc('staff_id')
            ->value('staff_id');

        $lastUserId = Schema::hasTable('users') && Schema::hasColumn('users', 'hospital_id')
            ? DB::table('users')->where('hospital_id', 'like', 'S%')->orderByDesc('hospital_id')->value('hospital_id')
            : null;

        $next = max(
            (int) preg_replace('/\D/', '', (string) $lastStaffId),
            (int) preg_replace('/\D/', '', (string) $lastUserId),
        ) + 1;

        do {
            $staffId = 'S' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (
            DB::table('staff')->where('staff_id', $staffId)->exists()
            || (
                Schema::hasTable('users')
                && Schema::hasColumn('users', 'hospital_id')
                && DB::table('users')->where('hospital_id', $staffId)->exists()
            )
        );

        return $staffId;
    }

    protected function validateAppointment(Request $request): array
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,patient_id'],
            'consultant_id' => ['required', 'exists:staff,staff_id'],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2099-12-31'],
            'appointment_time' => ['required'],
            'room' => ['required', 'string', 'max:50'],
            'recommendation' => ['nullable', 'string', 'max:30'],
            'admission_ward_id' => ['nullable', 'exists:wards,ward_id'],
            'admission_bed_id' => ['nullable', 'exists:beds,bed_id'],
        ], [
            'appointment_date.after_or_equal' => 'Appointment date must be today or a future date.',
            'appointment_date.before_or_equal' => 'Appointment date must use a valid year.',
        ]);

        if (! Schema::hasColumn('appointments', 'recommendation')) {
            unset($validated['recommendation']);
        }

        return $validated;
    }

    protected function appointmentColumns(array $validated): array
    {
        return collect($validated)->only([
            'patient_id',
            'consultant_id',
            'appointment_date',
            'appointment_time',
            'room',
            'recommendation',
        ])->all();
    }

    protected function syncAppointmentRecommendation(string|int $appointmentId, array $validated): void
    {
        $recommendation = $validated['recommendation'] ?? null;
        $patientId = $validated['patient_id'];

        if (Schema::hasTable('outpatients')) {
            if ($recommendation === 'Out-Patient Clinic') {
                $existing = DB::table('outpatients')->where('appointment_id', $appointmentId)->exists();

                if ($existing) {
                    DB::table('outpatients')->where('appointment_id', $appointmentId)->update([
                        'patient_id' => $patientId,
                    ]);
                } else {
                    DB::table('outpatients')->insert([
                        'patient_id' => $patientId,
                        'appointment_id' => $appointmentId,
                    ] + $this->keyForInsert('outpatients', 'outpatient_id', 'OP', 5, (string) $patientId));
                }
            } else {
                DB::table('outpatients')->where('appointment_id', $appointmentId)->delete();
            }
        }

        if (! in_array($recommendation, ['Waiting List', 'In-Patient Admission'], true) || ! Schema::hasTable('admissions')) {
            return;
        }

        $wardId = $validated['admission_ward_id'] ?? DB::table('wards')->orderBy('ward_id')->value('ward_id');

        if (! $wardId) {
            return;
        }

        $bedId = $validated['admission_bed_id'] ?? null;
        $admission = $this->onlyExistingColumns('admissions', [
            'patient_id' => $patientId,
            'ward_id' => $wardId,
            'bed_id' => $bedId,
            'date_placed_on_waiting_list' => $validated['appointment_date'] ?? now()->toDateString(),
            'date_admitted' => $recommendation === 'In-Patient Admission' ? ($validated['appointment_date'] ?? now()->toDateString()) : null,
            'actual_leave_date' => null,
        ]);

        $existingAdmissionId = DB::table('admissions')
            ->where('patient_id', $patientId)
            ->whereNull('actual_leave_date')
            ->orderByDesc('admission_id')
            ->value('admission_id');

        if ($existingAdmissionId) {
            $previousBedId = DB::table('admissions')->where('admission_id', $existingAdmissionId)->value('bed_id');
            DB::table('admissions')->where('admission_id', $existingAdmissionId)->update($admission);

            if ($previousBedId) {
                $this->syncBedStatus($previousBedId);
            }
        } else {
            DB::table('admissions')->insert($admission + $this->keyForInsert('admissions', 'admission_id', 'A', 5, (string) $patientId));
        }

        if ($bedId) {
            $this->syncBedStatus($bedId);
        }
    }

    protected function validateMedication(Request $request): array
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,patient_id'],
            'drug_id' => ['required', 'exists:drugs,drug_id'],
            'units_per_day' => ['required', 'integer', 'min:1'],
            'method_of_admin' => ['nullable', 'string', 'max:80'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if (! Schema::hasColumn('patient_medication', 'method_of_admin')) {
            unset($validated['method_of_admin']);
        }

        return $validated;
    }

    protected function keyForInsert(string $table, string $column, string $prefix, int $width = 3, ?string $preferredSource = null): array
    {
        if (! $this->usesStringKey($table, $column)) {
            return [];
        }

        return [$column => $this->nextPrefixedId($table, $column, $prefix, $width, $preferredSource)];
    }

    protected function nextPrefixedId(string $table, string $column, string $prefix, int $width = 3, ?string $preferredSource = null): string
    {
        $number = $preferredSource ? (int) preg_replace('/\D/', '', $preferredSource) : 0;

        if ($number <= 0) {
            $last = DB::table($table)
                ->where($column, 'like', $prefix . '%')
                ->orderByDesc($column)
                ->value($column);

            $number = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;
        }

        do {
            $id = $prefix . str_pad((string) $number, $width, '0', STR_PAD_LEFT);
            $number++;
        } while (DB::table($table)->where($column, $id)->exists());

        return $id;
    }

    protected function onlyExistingColumns(string $table, array $row): array
    {
        return collect($row)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    protected function authorizeModule(string $module): void
    {
        $role = auth()->user()?->role;

        abort_unless(in_array($role, self::PERMISSIONS[$module] ?? [], true), 403);
    }

    private function hospitalData(): array
    {
        try {
            $patients = collect();
            $staff = collect();
            $wards = collect();
            $beds = collect();
            $admissions = collect();
            $appointments = collect();
            $outpatients = collect();
            $medications = collect();
            $drugs = collect();
            $rotas = collect();
            $accountRequests = collect();
            $localDoctors = collect();

            if (Schema::hasTable('patients')) {
                $patients = DB::table('patients')
                    ->leftJoin('admissions', function ($join) {
                        $join->on('patients.patient_id', '=', 'admissions.patient_id')
                            ->whereNull('admissions.actual_leave_date');
                    })
                    ->leftJoin('wards', 'admissions.ward_id', '=', 'wards.ward_id')
                    ->leftJoin('beds', 'admissions.bed_id', '=', 'beds.bed_id')
                    ->select('patients.*', 'wards.ward_name', 'wards.tel_extension', 'beds.bed_number', 'admissions.date_admitted', 'admissions.date_placed_on_waiting_list')
                    ->orderBy('patients.patient_id')
                    ->get();
                $patients = $this->attachPatientDetails($patients)->unique('patient_id')->values();
            }

            $staffPositionSelect = Schema::hasTable('staff') && Schema::hasColumn('staff', 'position')
                ? DB::raw('staff.position as position')
                : DB::raw('staff.staff_position as position');

            if (Schema::hasTable('staff')) {
                $staff = DB::table('staff')
                    ->leftJoin('ward_staff', 'staff.staff_id', '=', 'ward_staff.staff_id')
                    ->leftJoin('wards', 'ward_staff.ward_id', '=', 'wards.ward_id')
                    ->select('staff.*', $staffPositionSelect, 'ward_staff.ward_id', 'ward_staff.role', 'ward_staff.shift', 'wards.ward_name', 'wards.tel_extension')
                    ->orderBy('staff.staff_id')
                    ->get();
                $staff = $this->attachStaffDetails($staff);
            }

            if (Schema::hasTable('wards')) {
                $wards = DB::table('wards')
                    ->leftJoin('beds', 'wards.ward_id', '=', 'beds.ward_id')
                    ->leftJoin('ward_staff', 'wards.ward_id', '=', 'ward_staff.ward_id')
                    ->leftJoin('admissions', function ($join) {
                        $join->on('wards.ward_id', '=', 'admissions.ward_id')
                            ->whereNull('admissions.actual_leave_date');
                    })
                    ->select(
                        'wards.*',
                        DB::raw('count(distinct admissions.bed_id) as occupied_beds'),
                        DB::raw('count(distinct beds.bed_id) as tracked_beds'),
                        DB::raw('count(distinct ward_staff.staff_id) as staff_count')
                    )
                    ->groupBy('wards.ward_id', 'wards.ward_name', 'wards.location', 'wards.total_beds', 'wards.tel_extension')
                    ->orderBy('wards.ward_id')
                    ->get()
                    ->sortBy(fn ($ward) => (int) preg_replace('/\D+/', '', (string) $ward->ward_id))
                    ->values();
            }

            if (Schema::hasTable('beds')) {
                $beds = DB::table('beds')->get();
            }

            if (Schema::hasTable('admissions')) {
                $admissions = DB::table('admissions')
                    ->join('patients', 'admissions.patient_id', '=', 'patients.patient_id')
                    ->join('wards', 'admissions.ward_id', '=', 'wards.ward_id')
                    ->leftJoin('beds', 'admissions.bed_id', '=', 'beds.bed_id')
                    ->select('admissions.*', 'patients.first_name', 'patients.last_name', 'wards.ward_name', 'beds.bed_number')
                    ->orderBy('admissions.ward_id')
                    ->orderByDesc('admissions.date_admitted')
                    ->get();
            }

            if (Schema::hasTable('appointments')) {
                $appointments = DB::table('appointments')
                    ->join('patients', 'appointments.patient_id', '=', 'patients.patient_id')
                    ->leftJoin('staff', 'appointments.consultant_id', '=', 'staff.staff_id')
                    ->select('appointments.*', 'patients.first_name as patient_first_name', 'patients.last_name as patient_last_name', 'patients.address as patient_address', 'patients.phone as patient_phone', 'patients.date_of_birth as patient_date_of_birth', 'patients.sex as patient_sex', 'staff.first_name as consultant_first_name', 'staff.last_name as consultant_last_name')
                    ->orderBy('appointment_date')
                    ->orderBy('appointment_time')
                    ->get();
            }

            if (Schema::hasTable('outpatients')) {
                $outpatients = DB::table('outpatients')
                    ->join('patients', 'outpatients.patient_id', '=', 'patients.patient_id')
                    ->join('appointments', 'outpatients.appointment_id', '=', 'appointments.appointment_id')
                    ->select('outpatients.*', 'patients.first_name', 'patients.last_name', 'patients.address', 'patients.phone', 'patients.date_of_birth', 'patients.sex', 'appointments.appointment_date', 'appointments.appointment_time', 'appointments.room')
                    ->orderBy('appointments.appointment_date')
                    ->orderBy('appointments.appointment_time')
                    ->get();
            }

            if (Schema::hasTable('patient_medication')) {
                $hasMedicationMethod = Schema::hasColumn('patient_medication', 'method_of_admin');
                $hasDrugMethod = Schema::hasColumn('drugs', 'method_of_admin');
                $medicationMethodSelect = match (true) {
                    $hasMedicationMethod && $hasDrugMethod => DB::raw('coalesce(patient_medication.method_of_admin, drugs.method_of_admin) as method_of_admin'),
                    $hasMedicationMethod => DB::raw('patient_medication.method_of_admin as method_of_admin'),
                    $hasDrugMethod => DB::raw('drugs.method_of_admin as method_of_admin'),
                    default => DB::raw("null as method_of_admin"),
                };

                $medications = DB::table('patient_medication')
                    ->join('patients', 'patient_medication.patient_id', '=', 'patients.patient_id')
                    ->join('drugs', 'patient_medication.drug_id', '=', 'drugs.drug_id')
                    ->leftJoin('admissions', function ($join) {
                        $join->on('patients.patient_id', '=', 'admissions.patient_id')
                            ->whereNull('admissions.actual_leave_date');
                    })
                    ->leftJoin('wards', 'admissions.ward_id', '=', 'wards.ward_id')
                    ->leftJoin('beds', 'admissions.bed_id', '=', 'beds.bed_id')
                    ->select('patient_medication.*', 'patients.first_name', 'patients.last_name', 'drugs.name as drug_name', 'drugs.description', 'drugs.dosage', $medicationMethodSelect, 'wards.ward_name', 'beds.bed_number')
                    ->orderByDesc('patient_medication.start_date')
                    ->get();
            }

            if (Schema::hasTable('drugs')) {
                $drugs = DB::table('drugs')->orderBy('name')->get();
            }

            if (Schema::hasTable('local_doctors')) {
                $localDoctors = DB::table('local_doctors')->orderBy('full_name')->get();
            }

            if (Schema::hasTable('staff_rotas')) {
                $rotas = DB::table('staff_rotas')
                    ->join('staff', 'staff_rotas.staff_id', '=', 'staff.staff_id')
                    ->leftJoin('wards', 'staff_rotas.ward_id', '=', 'wards.ward_id')
                    ->select('staff_rotas.*', 'staff.first_name', 'staff.last_name', $staffPositionSelect, 'wards.ward_name')
                    ->orderByDesc('week_start_date')
                    ->orderBy('shift')
                    ->get();
            }

            if (Schema::hasTable('account_requests')) {
                $accountRequests = DB::table('account_requests')
                    ->where('status', 'Pending')
                    ->orderBy('created_at')
                    ->get();
            }

            $wards = $this->attachWardDetails($wards, $staff, $admissions);

            return $this->shapeData($patients, $staff, $wards, $beds, $admissions, $appointments, $outpatients, $medications, $drugs, $rotas, $accountRequests, $localDoctors, false);
        } catch (Throwable $exception) {
            Log::error('Wellmeadows dashboard database query failed.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->fallbackData($exception->getMessage());
        }
    }

    private function shapeData($patients, $staff, $wards, $beds, $admissions, $appointments, $outpatients, $medications, $drugs, $rotas, $accountRequests, $localDoctors, bool $offline): array
    {
        $today = Carbon::today()->toDateString();
        $occupiedBeds = $wards->isNotEmpty() ? (int) $wards->sum('occupied_beds') : $beds->where('status', 'Occupied')->count();
        $totalBeds = max((int) $wards->sum('total_beds'), $beds->count());
            $consultants = $staff->filter(fn ($member) => str_contains(strtolower($member->position ?? ''), 'doctor') || str_contains(strtolower($member->position ?? ''), 'consultant'));

        return [
            'offline' => $offline,
            'permissions' => $this->permissionsForCurrentUser(),
            'patients' => $patients,
            'staff' => $staff,
            'consultants' => $consultants,
            'wards' => $wards,
            'beds' => $beds,
            'admissions' => $admissions,
            'appointments' => $appointments,
            'outpatients' => $outpatients,
            'medications' => $medications,
            'drugs' => $drugs,
            'rotas' => $rotas,
            'accountRequests' => $accountRequests,
            'localDoctors' => $localDoctors,
            'stats' => [
                'patients' => $patients->count(),
                'staff' => $staff->count(),
                'doctors' => $consultants->count(),
                'nurses' => $staff->filter(fn ($member) => str_contains(strtolower($member->position ?? ''), 'nurse'))->count(),
                'wards' => $wards->count(),
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'occupancy' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0,
                'today_appointments' => $appointments->where('appointment_date', $today)->count(),
                'outpatients' => $outpatients->count(),
                'active_prescriptions' => $medications->filter(fn ($medication) => empty($medication->end_date) || $medication->end_date >= $today)->count(),
                'drug_types' => $drugs->count(),
                'pending_account_requests' => $accountRequests->count(),
            ],
        ];
    }

    private function attachPatientDetails($patients)
    {
        if ($patients->isEmpty()) {
            return $patients;
        }

        $patientIds = $patients->pluck('patient_id')->unique()->values();
        $kin = Schema::hasTable('next_of_kin')
            ? DB::table('next_of_kin')->whereIn('patient_id', $patientIds)->orderBy('kin_id')->get()->unique('patient_id')->keyBy('patient_id')
            : collect();
        $referrals = collect();

        if (Schema::hasTable('patient_referrals') && Schema::hasTable('local_doctors')) {
            $referrals = DB::table('patient_referrals')
                ->join('local_doctors', 'patient_referrals.doctor_id', '=', 'local_doctors.doctor_id')
                ->whereIn('patient_referrals.patient_id', $patientIds)
                ->select('patient_referrals.patient_id', 'patient_referrals.referral_date', 'local_doctors.full_name as doctor_name', 'local_doctors.clinic_number', 'local_doctors.address as doctor_address', 'local_doctors.phone as doctor_phone')
                ->orderBy('patient_referrals.referral_id')
                ->get()
                ->unique('patient_id')
                ->keyBy('patient_id');
        }

        $appointments = collect();

        if (Schema::hasTable('appointments')) {
            $appointments = DB::table('appointments')
                ->leftJoin('staff', 'appointments.consultant_id', '=', 'staff.staff_id')
                ->whereIn('appointments.patient_id', $patientIds)
                ->select('appointments.*', 'staff.first_name as consultant_first_name', 'staff.last_name as consultant_last_name')
                ->orderByDesc('appointments.appointment_date')
                ->orderByDesc('appointments.appointment_time')
                ->get()
                ->unique('patient_id')
                ->keyBy('patient_id');
        }

        return $patients->map(function ($patient) use ($kin, $referrals, $appointments) {
            $patientKin = $kin->get($patient->patient_id);
            $referral = $referrals->get($patient->patient_id);
            $appointment = $appointments->get($patient->patient_id);

            $patient->kin_name = $patientKin->full_name ?? null;
            $patient->kin_relationship = $patientKin->relationship ?? null;
            $patient->kin_address = $patientKin->address ?? null;
            $patient->kin_phone = $patientKin->phone ?? null;
            $patient->doctor_name = $referral->doctor_name ?? null;
            $patient->clinic_number = $referral->clinic_number ?? null;
            $patient->doctor_address = $referral->doctor_address ?? null;
            $patient->doctor_phone = $referral->doctor_phone ?? null;
            $patient->referral_date = $referral->referral_date ?? null;
            $patient->appointment_id = $appointment->appointment_id ?? null;
            $patient->appointment_date = $appointment->appointment_date ?? null;
            $patient->appointment_time = $appointment->appointment_time ?? null;
            $patient->appointment_room = $appointment->room ?? null;
            $patient->appointment_recommendation = $appointment->recommendation ?? null;
            $patient->appointment_consultant = trim(($appointment->consultant_first_name ?? '') . ' ' . ($appointment->consultant_last_name ?? '')) ?: null;

            return $patient;
        });
    }

    private function attachStaffDetails($staff)
    {
        if ($staff->isEmpty()) {
            return $staff;
        }

        $staffIds = $staff->pluck('staff_id')->unique()->values();
        $contracts = Schema::hasTable('staff_contracts')
            ? DB::table('staff_contracts')->whereIn('staff_id', $staffIds)->orderBy('contract_id')->get()->unique('staff_id')->keyBy('staff_id')
            : collect();
        $qualifications = Schema::hasTable('qualifications')
            ? DB::table('qualifications')->whereIn('staff_id', $staffIds)->orderBy('qualification_id')->get()->unique('staff_id')->keyBy('staff_id')
            : collect();
        $experiences = Schema::hasTable('work_experience')
            ? DB::table('work_experience')->whereIn('staff_id', $staffIds)->orderBy('experience_id')->get()->unique('staff_id')->keyBy('staff_id')
            : collect();

        return $staff->map(function ($member) use ($contracts, $qualifications, $experiences) {
            $contract = $contracts->get($member->staff_id);
            $qualification = $qualifications->get($member->staff_id);
            $experience = $experiences->get($member->staff_id);

            $member->hours_per_week = $contract->hours_per_week ?? null;
            $member->contract_type = $contract->contract_type ?? null;
            $member->payment_type = $contract->payment_type ?? null;
            $member->qualification_type = $qualification->qualification_type ?? null;
            $member->institution = $qualification->institution ?? null;
            $member->date_obtained = $qualification->date_obtained ?? null;
            $member->experience_position = $experience->staff_position ?? null;
            $member->organization = $experience->organization ?? null;
            $member->start_date = $experience->start_date ?? null;
            $member->end_date = $experience->end_date ?? null;

            return $member;
        });
    }

    private function attachWardDetails($wards, $staff, $admissions)
    {
        return $wards->map(function ($ward) use ($staff, $admissions) {
            $ward->assigned_staff = $staff->where('ward_name', $ward->ward_name)->values();
            $ward->ward_admissions = $admissions->where('ward_id', $ward->ward_id)->values();
            $ward->active_patients = $ward->ward_admissions->whereNull('actual_leave_date')->values();
            $ward->discharged_patients = $ward->ward_admissions->whereNotNull('actual_leave_date')->values();

            return $ward;
        });
    }

    private function fallbackData(?string $databaseError = null): array
    {
        $patients = collect([
            (object) ['patient_id' => 1, 'first_name' => 'Anne', 'last_name' => 'Phelps', 'address' => '44 North Briges Cannomills Edinburgh', 'phone' => '0132-332-4111', 'date_of_birth' => '1933-12-12', 'sex' => 'Female', 'marital_status' => 'Single', 'date_registered' => '1995-02-21', 'ward_name' => 'Orthopaedic', 'bed_number' => 84, 'date_admitted' => '2026-05-08'],
            (object) ['patient_id' => 2, 'first_name' => 'Robert', 'last_name' => 'MacDonald', 'address' => '123 High Street', 'phone' => '0131-550-8899', 'date_of_birth' => '1948-03-20', 'sex' => 'Male', 'marital_status' => 'Married', 'date_registered' => '2026-01-10', 'ward_name' => 'Orthopaedic', 'bed_number' => 79, 'date_admitted' => '2026-05-08'],
            (object) ['patient_id' => 3, 'first_name' => 'David', 'last_name' => 'Black', 'address' => '89 Princes Street', 'phone' => '0131-225-7700', 'date_of_birth' => '1953-11-09', 'sex' => 'Male', 'marital_status' => 'Single', 'date_registered' => '2026-03-12', 'ward_name' => null, 'bed_number' => null, 'date_admitted' => null],
        ]);

        $staff = collect([
            (object) ['staff_id' => 1, 'first_name' => 'Moira', 'last_name' => 'Samuel', 'position' => 'Charge Nurse', 'address' => '49 School Road Broxburn', 'phone' => '01506-45633', 'date_of_birth' => '1961-05-30', 'sex' => 'Female', 'nin' => 'WB123423D', 'salary' => 18760.00, 'salary_scale' => '1C', 'ward_name' => 'Orthopaedic', 'tel_extension' => '7711', 'shift' => 'Early', 'hours_per_week' => 40.00, 'contract_type' => 'Permanent', 'payment_type' => 'Monthly', 'qualification_type' => 'Bachelor of Science in Nursing', 'institution' => 'University of Edinburgh', 'date_obtained' => '2010-06-15', 'experience_position' => 'Senior Resident', 'organization' => 'Jr. Borja General Hospital', 'start_date' => '2012-06-01', 'end_date' => '2025-12-31'],
            (object) ['staff_id' => 2, 'first_name' => 'Anna', 'last_name' => 'Cruz', 'position' => 'Nurse', 'address' => 'CDO', 'phone' => '09170001111', 'date_of_birth' => '1988-07-12', 'sex' => 'Female', 'nin' => 'AB123', 'salary' => 15000.00, 'salary_scale' => '1A', 'ward_name' => 'Out-Patient Clinic', 'tel_extension' => '7722', 'shift' => 'Late', 'hours_per_week' => 40.00, 'contract_type' => 'Permanent', 'payment_type' => 'Monthly', 'qualification_type' => 'Doctor of Medicine (MD)', 'institution' => 'Harvard Medical School', 'date_obtained' => '2008-05-20', 'experience_position' => 'Staff Nurse', 'organization' => 'Jr. Borja General Hospital', 'start_date' => '2014-03-01', 'end_date' => '2024-12-15'],
            (object) ['staff_id' => 3, 'first_name' => 'Leo', 'last_name' => 'Martinez', 'position' => 'Doctor', 'address' => 'CDO', 'phone' => '09170002222', 'date_of_birth' => '1980-02-20', 'sex' => 'Male', 'nin' => 'CD456', 'salary' => 30000.00, 'salary_scale' => '2B', 'ward_name' => 'General', 'tel_extension' => '7713', 'shift' => 'Night', 'hours_per_week' => 38.00, 'contract_type' => 'Permanent', 'payment_type' => 'Monthly', 'qualification_type' => 'Bachelor of Science in Nursing', 'institution' => 'Johns Hopkins University', 'date_obtained' => '2009-03-20', 'experience_position' => 'Junior Doctor', 'organization' => 'Jr. Borja General Hospital', 'start_date' => '2014-01-10', 'end_date' => '2025-06-30'],
        ]);

        $wards = collect([
            (object) ['ward_id' => 1, 'ward_name' => 'Orthopaedic', 'location' => 'Block E', 'total_beds' => 20, 'tel_extension' => '7711', 'occupied_beds' => 1, 'staff_count' => 1],
            (object) ['ward_id' => 2, 'ward_name' => 'Pediatrics', 'location' => 'Block A', 'total_beds' => 15, 'tel_extension' => '7712', 'occupied_beds' => 1, 'staff_count' => 1],
            (object) ['ward_id' => 3, 'ward_name' => 'General', 'location' => 'Block B', 'total_beds' => 25, 'tel_extension' => '7713', 'occupied_beds' => 0, 'staff_count' => 1],
        ]);

        $beds = collect([
            (object) ['status' => 'Occupied'],
            (object) ['status' => 'Available'],
            (object) ['status' => 'Occupied'],
            (object) ['status' => 'Available'],
        ]);

        $appointments = collect([
            (object) ['appointment_id' => 'AP10234', 'patient_id' => 'P10234', 'consultant_id' => 'S344', 'patient_first_name' => 'Anne', 'patient_last_name' => 'Phelps', 'patient_address' => '44 North Briges Cannomills Edinburgh', 'patient_phone' => '0132-332-4111', 'patient_date_of_birth' => '1933-12-12', 'patient_sex' => 'Female', 'consultant_first_name' => 'Helen', 'consultant_last_name' => 'Pearson', 'appointment_date' => now()->toDateString(), 'appointment_time' => '09:00:00', 'room' => 'E252', 'recommendation' => 'Out-Patient Clinic'],
        ]);

        $drugs = collect([(object) ['drug_id' => 'D10223', 'name' => 'Morphine']]);
        $localDoctors = collect([
            (object) ['doctor_id' => 'LD001', 'full_name' => 'Dr. Helen Pearson', 'clinic_number' => 'CL001', 'address' => 'Edinburgh Clinic', 'phone' => null],
        ]);
        $medications = collect([
            (object) ['medication_id' => 1, 'patient_id' => 'P10234', 'drug_id' => 'D10223', 'first_name' => 'Anne', 'last_name' => 'Phelps', 'drug_name' => 'Morphine', 'description' => 'Pain killer', 'dosage' => '10mg/ml', 'method_of_admin' => 'Oral', 'units_per_day' => 5, 'start_date' => now()->subDays(3)->toDateString(), 'end_date' => now()->addDays(7)->toDateString(), 'ward_name' => 'Orthopaedic', 'bed_number' => 84],
        ]);
        $outpatients = collect([
            (object) ['outpatient_id' => 1, 'patient_id' => 1, 'first_name' => 'Anne', 'last_name' => 'Phelps', 'address' => '44 North Briges Cannomills Edinburgh', 'phone' => '0132-332-4111', 'date_of_birth' => '1933-12-12', 'sex' => 'Female', 'appointment_date' => now()->toDateString(), 'appointment_time' => '09:00:00', 'room' => 'E252'],
        ]);
        $rotas = collect([
            (object) ['rota_id' => 1, 'first_name' => 'Moira', 'last_name' => 'Samuel', 'position' => 'Charge Nurse', 'ward_name' => 'Orthopaedic', 'week_start_date' => now()->startOfWeek()->toDateString(), 'shift' => 'Early', 'duty_area' => 'Ward 1'],
        ]);

        $data = $this->shapeData($patients, $staff, $wards, $beds, collect(), $appointments, $outpatients, $medications, $drugs, $rotas, collect(), $localDoctors, true);
        $data['databaseError'] = $databaseError;

        return $data;
    }

    private function permissionsForCurrentUser(): array
    {
        $role = auth()->user()?->role;

        return collect(self::PERMISSIONS)
            ->map(fn (array $roles) => in_array($role, $roles, true))
            ->all();
    }
}
