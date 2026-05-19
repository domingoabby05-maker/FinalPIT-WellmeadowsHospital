<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HospitalController extends Controller
{
    public function index()
    {
        $data = $this->hospitalData();

        return view('hospital.dashboard', $data);
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date'],
            'sex' => ['required', 'in:M,F'],
            'marital_status' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['date_registered'] = now()->toDateString();

        DB::table('patients')->insert($validated);

        return back()->with('status', 'Patient registered successfully.');
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date'],
            'sex' => ['required', 'in:M,F'],
            'nin' => ['required', 'string', 'max:30'],
            'staff_position' => ['required', 'string', 'max:100'],
            'salary' => ['required', 'numeric', 'min:0'],
            'salary_scale' => ['nullable', 'string', 'max:20'],
        ]);

        DB::table('staff')->insert($validated);

        return back()->with('status', 'Staff record added successfully.');
    }

    public function storeAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,patient_id'],
            'consultant_id' => ['required', 'integer', 'exists:staff,staff_id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],
            'room' => ['required', 'string', 'max:50'],
        ]);

        DB::table('appointments')->insert($validated);

        return back()->with('status', 'Appointment scheduled successfully.');
    }

    public function storeMedication(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,patient_id'],
            'drug_id' => ['required', 'integer', 'exists:drugs,drug_id'],
            'units_per_day' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        DB::table('patient_medication')->insert($validated);

        return back()->with('status', 'Medication prescribed successfully.');
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
            $medications = collect();
            $drugs = collect();

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
            }

            if (Schema::hasTable('staff')) {
                $staff = DB::table('staff')
                    ->leftJoin('ward_staff', 'staff.staff_id', '=', 'ward_staff.staff_id')
                    ->leftJoin('wards', 'ward_staff.ward_id', '=', 'wards.ward_id')
                    ->select('staff.*', 'ward_staff.role', 'ward_staff.shift', 'wards.ward_name', 'wards.tel_extension')
                    ->orderBy('staff.staff_id')
                    ->get();
            }

            if (Schema::hasTable('wards')) {
                $wards = DB::table('wards')
                    ->leftJoin('beds', 'wards.ward_id', '=', 'beds.ward_id')
                    ->leftJoin('ward_staff', 'wards.ward_id', '=', 'ward_staff.ward_id')
                    ->select(
                        'wards.*',
                        DB::raw("sum(case when beds.status = 'Occupied' then 1 else 0 end) as occupied_beds"),
                        DB::raw('count(distinct beds.bed_id) as tracked_beds'),
                        DB::raw('count(distinct ward_staff.staff_id) as staff_count')
                    )
                    ->groupBy('wards.ward_id', 'wards.ward_name', 'wards.location', 'wards.total_beds', 'wards.tel_extension')
                    ->orderBy('wards.ward_id')
                    ->get();
            }

            if (Schema::hasTable('beds')) {
                $beds = DB::table('beds')->get();
            }

            if (Schema::hasTable('admissions')) {
                $admissions = DB::table('admissions')->whereNull('actual_leave_date')->get();
            }

            if (Schema::hasTable('appointments')) {
                $appointments = DB::table('appointments')
                    ->join('patients', 'appointments.patient_id', '=', 'patients.patient_id')
                    ->leftJoin('staff', 'appointments.consultant_id', '=', 'staff.staff_id')
                    ->select('appointments.*', 'patients.first_name as patient_first_name', 'patients.last_name as patient_last_name', 'staff.first_name as consultant_first_name', 'staff.last_name as consultant_last_name')
                    ->orderBy('appointment_date')
                    ->orderBy('appointment_time')
                    ->get();
            }

            if (Schema::hasTable('patient_medication')) {
                $medications = DB::table('patient_medication')
                    ->join('patients', 'patient_medication.patient_id', '=', 'patients.patient_id')
                    ->join('drugs', 'patient_medication.drug_id', '=', 'drugs.drug_id')
                    ->leftJoin('admissions', function ($join) {
                        $join->on('patients.patient_id', '=', 'admissions.patient_id')
                            ->whereNull('admissions.actual_leave_date');
                    })
                    ->leftJoin('wards', 'admissions.ward_id', '=', 'wards.ward_id')
                    ->leftJoin('beds', 'admissions.bed_id', '=', 'beds.bed_id')
                    ->select('patient_medication.*', 'patients.first_name', 'patients.last_name', 'drugs.name as drug_name', 'drugs.description', 'drugs.dosage', 'drugs.method_of_admin', 'wards.ward_name', 'beds.bed_number')
                    ->orderByDesc('patient_medication.start_date')
                    ->get();
            }

            if (Schema::hasTable('drugs')) {
                $drugs = DB::table('drugs')->orderBy('name')->get();
            }

            return $this->shapeData($patients, $staff, $wards, $beds, $admissions, $appointments, $medications, $drugs, false);
        } catch (Throwable) {
            return $this->fallbackData();
        }
    }

    private function shapeData($patients, $staff, $wards, $beds, $admissions, $appointments, $medications, $drugs, bool $offline): array
    {
        $today = Carbon::today()->toDateString();
        $occupiedBeds = $beds->where('status', 'Occupied')->count();
        $totalBeds = max((int) $wards->sum('total_beds'), $beds->count());
        $consultants = $staff->filter(fn ($member) => str_contains(strtolower($member->staff_position ?? ''), 'doctor') || str_contains(strtolower($member->staff_position ?? ''), 'consultant'));

        return [
            'offline' => $offline,
            'patients' => $patients,
            'staff' => $staff,
            'consultants' => $consultants,
            'wards' => $wards,
            'beds' => $beds,
            'admissions' => $admissions,
            'appointments' => $appointments,
            'medications' => $medications,
            'drugs' => $drugs,
            'stats' => [
                'patients' => $patients->count(),
                'staff' => $staff->count(),
                'doctors' => $consultants->count(),
                'nurses' => $staff->filter(fn ($member) => str_contains(strtolower($member->staff_position ?? ''), 'nurse'))->count(),
                'wards' => $wards->count(),
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'occupancy' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0,
                'today_appointments' => $appointments->where('appointment_date', $today)->count(),
                'active_prescriptions' => $medications->filter(fn ($medication) => empty($medication->end_date) || $medication->end_date >= $today)->count(),
                'drug_types' => $drugs->count(),
            ],
        ];
    }

    private function fallbackData(): array
    {
        $patients = collect([
            (object) ['patient_id' => 1, 'first_name' => 'Anne', 'last_name' => 'Phelps', 'address' => '44 North Briges Cannomills Edinburgh', 'phone' => '0132-332-4111', 'date_of_birth' => '1933-12-12', 'sex' => 'F', 'marital_status' => 'Single', 'date_registered' => '1995-02-21', 'ward_name' => 'Orthopaedic', 'bed_number' => 84, 'date_admitted' => '2026-05-08'],
            (object) ['patient_id' => 2, 'first_name' => 'Robert', 'last_name' => 'MacDonald', 'address' => '123 High Street', 'phone' => '0131-550-8899', 'date_of_birth' => '1948-03-20', 'sex' => 'M', 'marital_status' => 'Married', 'date_registered' => '2026-01-10', 'ward_name' => 'Orthopaedic', 'bed_number' => 79, 'date_admitted' => '2026-05-08'],
            (object) ['patient_id' => 3, 'first_name' => 'David', 'last_name' => 'Black', 'address' => '89 Princes Street', 'phone' => '0131-225-7700', 'date_of_birth' => '1953-11-09', 'sex' => 'M', 'marital_status' => 'Single', 'date_registered' => '2026-03-12', 'ward_name' => null, 'bed_number' => null, 'date_admitted' => null],
        ]);

        $staff = collect([
            (object) ['staff_id' => 1, 'first_name' => 'Moira', 'last_name' => 'Samuel', 'staff_position' => 'Charge Nurse', 'address' => '49 School Road Broxburn', 'phone' => '01506-45633', 'sex' => 'F', 'ward_name' => 'Orthopaedic', 'tel_extension' => '7711', 'shift' => 'Morning'],
            (object) ['staff_id' => 2, 'first_name' => 'Loisa', 'last_name' => 'Nieves', 'staff_position' => 'Consultant', 'address' => 'CDO', 'phone' => '09872345436', 'sex' => 'F', 'ward_name' => 'Out-Patient Clinic', 'tel_extension' => '7722', 'shift' => 'Day'],
            (object) ['staff_id' => 3, 'first_name' => 'Leo', 'last_name' => 'Martinez', 'staff_position' => 'Doctor', 'address' => 'CDO', 'phone' => '09170002222', 'sex' => 'M', 'ward_name' => 'General', 'tel_extension' => '7713', 'shift' => 'Night'],
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
            (object) ['appointment_id' => 1, 'patient_id' => 1, 'patient_first_name' => 'Anne', 'patient_last_name' => 'Phelps', 'consultant_first_name' => 'Helen', 'consultant_last_name' => 'Pearson', 'appointment_date' => now()->toDateString(), 'appointment_time' => '09:00:00', 'room' => 'E252'],
        ]);

        $drugs = collect([(object) ['drug_id' => 1, 'name' => 'Morphine']]);
        $medications = collect([
            (object) ['medication_id' => 1, 'patient_id' => 1, 'first_name' => 'Anne', 'last_name' => 'Phelps', 'drug_name' => 'Morphine', 'description' => 'Pain killer', 'dosage' => '10mg/ml', 'method_of_admin' => 'Oral', 'units_per_day' => 5, 'start_date' => now()->subDays(3)->toDateString(), 'end_date' => now()->addDays(7)->toDateString(), 'ward_name' => 'Orthopaedic', 'bed_number' => 84],
        ]);

        return $this->shapeData($patients, $staff, $wards, $beds, collect(), $appointments, $medications, $drugs, true);
    }
}
