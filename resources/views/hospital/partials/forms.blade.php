<dialog id="patient-modal">
    <form method="POST" action="{{ route('patients.store') }}" class="modal-form" data-confirm="Add this patient record to Wellmeadows?">@csrf
        <h2>Register Patient</h2>
        <h3>Patient Information</h3>
        <input name="first_name" maxlength="50" placeholder="First name" required><input name="last_name" maxlength="50" placeholder="Last name" required>
        <textarea name="address" placeholder="Address" required></textarea><input name="phone" maxlength="20" placeholder="Telephone">
        <input name="date_of_birth" type="date" required><select name="sex" required><option value="Female">Female</option><option value="Male">Male</option></select>
        <select name="marital_status"><option value="">Marital status</option><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Divorced">Divorced</option></select>
        <h3>Next Of Kin</h3>
        <input name="kin_full_name" maxlength="100" placeholder="Next of kin full name"><input name="kin_relationship" maxlength="50" placeholder="Relationship">
        <textarea name="kin_address" placeholder="Next of kin address"></textarea><input name="kin_phone" maxlength="20" placeholder="Next of kin phone">
        <h3>Local Doctor And Referral</h3>
        <select data-local-doctor-select><option value="">Select local doctor</option>@foreach ($localDoctors as $doctor)<option value="{{ $doctor->doctor_id ?? $doctor->clinic_number }}" data-name="{{ $doctor->full_name }}" data-clinic="{{ $doctor->clinic_number }}" data-address="{{ $doctor->address }}" data-phone="{{ $doctor->phone }}">{{ $doctor->full_name }} - {{ $doctor->clinic_number }}</option>@endforeach</select>
        <input name="doctor_full_name" maxlength="100" placeholder="Local doctor full name"><input name="clinic_number" maxlength="50" placeholder="Clinic number">
        <textarea name="doctor_address" placeholder="Doctor clinic address"></textarea><input name="doctor_phone" maxlength="20" placeholder="Doctor phone">
        <input name="referral_date" type="date">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Patient</button></div>
    </form>
</dialog>

@foreach ($patients->unique('patient_id') as $patient)
    <dialog id="patient-edit-{{ $patient->patient_id }}">
        <form method="POST" action="{{ route('patients.update', $patient->patient_id) }}" class="modal-form" data-confirm="Save patient record changes for {{ $patient->first_name }} {{ $patient->last_name }}?">
            @csrf
            @method('PUT')
            <h2>Edit Patient</h2>
            <h3>Patient Information</h3>
            <input name="first_name" maxlength="50" value="{{ $patient->first_name }}" placeholder="First name" required><input name="last_name" maxlength="50" value="{{ $patient->last_name }}" placeholder="Last name" required>
            <textarea name="address" placeholder="Address" required>{{ $patient->address }}</textarea><input name="phone" maxlength="20" value="{{ $patient->phone }}" placeholder="Telephone">
            <input name="date_of_birth" type="date" value="{{ $patient->date_of_birth }}" required><select name="sex" required><option value="Female" @selected($patient->sex === 'Female')>Female</option><option value="Male" @selected($patient->sex === 'Male')>Male</option></select>
            <select name="marital_status"><option value="">Marital status</option><option value="Single" @selected(($patient->marital_status ?? '') === 'Single')>Single</option><option value="Married" @selected(($patient->marital_status ?? '') === 'Married')>Married</option><option value="Widowed" @selected(($patient->marital_status ?? '') === 'Widowed')>Widowed</option><option value="Divorced" @selected(($patient->marital_status ?? '') === 'Divorced')>Divorced</option></select>
            <h3>Next Of Kin</h3>
            <input name="kin_full_name" maxlength="100" value="{{ $patient->kin_name ?? '' }}" placeholder="Next of kin full name"><input name="kin_relationship" maxlength="50" value="{{ $patient->kin_relationship ?? '' }}" placeholder="Relationship">
            <textarea name="kin_address" placeholder="Next of kin address">{{ $patient->kin_address ?? '' }}</textarea><input name="kin_phone" maxlength="20" value="{{ $patient->kin_phone ?? '' }}" placeholder="Next of kin phone">
            <h3>Local Doctor And Referral</h3>
            <select data-local-doctor-select><option value="">Select local doctor</option>@foreach ($localDoctors as $doctor)<option value="{{ $doctor->doctor_id ?? $doctor->clinic_number }}" data-name="{{ $doctor->full_name }}" data-clinic="{{ $doctor->clinic_number }}" data-address="{{ $doctor->address }}" data-phone="{{ $doctor->phone }}" @selected(($patient->clinic_number ?? '') === $doctor->clinic_number)>{{ $doctor->full_name }} - {{ $doctor->clinic_number }}</option>@endforeach</select>
            <input name="doctor_full_name" maxlength="100" value="{{ $patient->doctor_name ?? '' }}" placeholder="Local doctor full name"><input name="clinic_number" maxlength="50" value="{{ $patient->clinic_number ?? '' }}" placeholder="Clinic number">
            <textarea name="doctor_address" placeholder="Doctor clinic address">{{ $patient->doctor_address ?? '' }}</textarea><input name="doctor_phone" maxlength="20" value="{{ $patient->doctor_phone ?? '' }}" placeholder="Doctor phone">
            <input name="referral_date" type="date" value="{{ $patient->referral_date ?? '' }}">
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Patient</button></div>
        </form>
    </dialog>
@endforeach

<dialog id="staff-modal">
    <form method="POST" action="{{ route('staff.store') }}" class="modal-form" data-confirm="Add this staff record to Wellmeadows?">@csrf
        <h2>Add Staff</h2>
        <h3>Staff Information</h3>
        <input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required>
        <textarea name="address" placeholder="Address" required></textarea><input name="phone" placeholder="Telephone">
        <input name="date_of_birth" type="date" required><select name="sex" required><option value="Female">Female</option><option value="Male">Male</option></select>
        <input name="nin" placeholder="National insurance number" required><input name="position" placeholder="Position" required>
        <input name="salary" type="number" step="0.01" placeholder="Current salary" required><input name="salary_scale" placeholder="Salary scale">
        <h3>Assigned Ward</h3>
        <select name="ward_id"><option value="">No ward assigned</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">{{ $ward->ward_name }} - {{ $ward->location ?? 'No location' }}</option>@endforeach</select>
        <input name="ward_role" list="ward-role-options" placeholder="Ward role"><input name="shift" list="shift-options" placeholder="Shift">
        <h3>Contract</h3>
        <input name="hours_per_week" type="number" step="0.01" placeholder="Hours per week"><input name="contract_type" list="contract-type-options" placeholder="Contract type">
        <input name="payment_type" list="payment-type-options" placeholder="Payment type">
        <h3>Qualification</h3>
        <input name="qualification_type" list="qualification-type-options" placeholder="Qualification type"><input name="institution" list="institution-options" placeholder="Institution">
        <input name="date_obtained" type="date">
        <h3>Work Experience</h3>
        <input name="experience_position" placeholder="Experience position"><input name="organization" placeholder="Organization">
        <input name="start_date" type="date"><input name="end_date" type="date">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Staff</button></div>
    </form>
</dialog>

@foreach ($staff as $member)
    <dialog id="staff-edit-{{ $member->staff_id }}">
        <form method="POST" action="{{ route('staff.update', $member->staff_id) }}" class="modal-form" data-confirm="Save staff record changes for {{ $member->first_name }} {{ $member->last_name }}?">
            @csrf
            @method('PUT')
            <h2>Edit Staff</h2>
            <h3>Staff Information</h3>
            <input name="first_name" value="{{ $member->first_name }}" placeholder="First name" required><input name="last_name" value="{{ $member->last_name }}" placeholder="Last name" required>
            <textarea name="address" placeholder="Address" required>{{ $member->address }}</textarea><input name="phone" value="{{ $member->phone }}" placeholder="Telephone">
            <input name="date_of_birth" type="date" value="{{ $member->date_of_birth }}" required><select name="sex" required><option value="Female" @selected($member->sex === 'Female')>Female</option><option value="Male" @selected($member->sex === 'Male')>Male</option></select>
            <input name="nin" value="{{ $member->nin }}" placeholder="National insurance number" required><input name="position" value="{{ $member->position ?? '' }}" placeholder="Position" required>
            <input name="salary" type="number" step="0.01" value="{{ $member->salary }}" placeholder="Current salary" required><input name="salary_scale" value="{{ $member->salary_scale }}" placeholder="Salary scale">
            <h3>Assigned Ward</h3>
            <select name="ward_id"><option value="">No ward assigned</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}" @selected(($member->ward_id ?? null) === $ward->ward_id)>{{ $ward->ward_name }} - {{ $ward->location ?? 'No location' }}</option>@endforeach</select>
            <input name="ward_role" list="ward-role-options" value="{{ $member->role ?? '' }}" placeholder="Ward role"><input name="shift" list="shift-options" value="{{ $member->shift ?? '' }}" placeholder="Shift">
            <h3>Contract</h3>
            <input name="hours_per_week" type="number" step="0.01" value="{{ $member->hours_per_week ?? '' }}" placeholder="Hours per week"><input name="contract_type" list="contract-type-options" value="{{ $member->contract_type ?? '' }}" placeholder="Contract type">
            <input name="payment_type" list="payment-type-options" value="{{ $member->payment_type ?? '' }}" placeholder="Payment type">
            <h3>Qualification</h3>
            <input name="qualification_type" list="qualification-type-options" value="{{ $member->qualification_type ?? '' }}" placeholder="Qualification type"><input name="institution" list="institution-options" value="{{ $member->institution ?? '' }}" placeholder="Institution">
            <input name="date_obtained" type="date" value="{{ $member->date_obtained ?? '' }}">
            <h3>Work Experience</h3>
            <input name="experience_position" value="{{ $member->experience_position ?? '' }}" placeholder="Experience position"><input name="organization" value="{{ $member->organization ?? '' }}" placeholder="Organization">
            <input name="start_date" type="date" value="{{ $member->start_date ?? '' }}"><input name="end_date" type="date" value="{{ $member->end_date ?? '' }}">
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Staff</button></div>
        </form>
    </dialog>
@endforeach

<dialog id="admission-modal">
    <form method="POST" action="{{ route('admissions.store') }}" class="modal-form" data-confirm="Add this ward admission or waiting-list record?">
        @csrf
        <h2>Add Ward Admission</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="ward_id" required>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">Ward {{ $ward->ward_id }} - {{ $ward->ward_name }}</option>@endforeach</select>
        <select name="bed_id" data-bed-select><option value="">Waiting list - no bed assigned</option>@foreach ($beds as $bed)@if ($bed->bed_id ?? null)<option value="{{ $bed->bed_id }}" data-ward-id="{{ $bed->ward_id ?? '' }}">Bed {{ $bed->bed_number ?? 'N/A' }} - Ward {{ $bed->ward_id ?? 'N/A' }} - {{ $bed->status ?? 'Available' }}</option>@endif @endforeach</select>
        <input name="date_placed_on_waiting_list" type="date" placeholder="Date placed on waiting list">
        <input name="expected_stay_days" type="number" min="1" placeholder="Expected stay days">
        <input name="date_admitted" type="date" placeholder="Date admitted">
        <input name="expected_leave_date" type="date" placeholder="Expected leave date">
        <input name="actual_leave_date" type="date" placeholder="Actual leave date">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Admission</button></div>
    </form>
</dialog>

@foreach ($admissions as $admission)
    <dialog id="admission-edit-{{ $admission->admission_id }}">
        <form method="POST" action="{{ route('admissions.update', $admission->admission_id) }}" class="modal-form" data-confirm="Save ward admission changes?">
            @csrf
            @method('PUT')
            <h2>Edit Ward Admission</h2>
            <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}" @selected($admission->patient_id === $patient->patient_id)>{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
            <select name="ward_id" required>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}" @selected($admission->ward_id === $ward->ward_id)>Ward {{ $ward->ward_id }} - {{ $ward->ward_name }}</option>@endforeach</select>
            <select name="bed_id" data-bed-select><option value="">Waiting list - no bed assigned</option>@foreach ($beds as $bed)@if ($bed->bed_id ?? null)<option value="{{ $bed->bed_id }}" data-ward-id="{{ $bed->ward_id ?? '' }}" @selected(($admission->bed_id ?? null) === $bed->bed_id)>Bed {{ $bed->bed_number ?? 'N/A' }} - Ward {{ $bed->ward_id ?? 'N/A' }} - {{ $bed->status ?? 'Available' }}</option>@endif @endforeach</select>
            <input name="date_placed_on_waiting_list" type="date" value="{{ $admission->date_placed_on_waiting_list }}">
            <input name="expected_stay_days" type="number" min="1" value="{{ $admission->expected_stay_days }}" placeholder="Expected stay days">
            <input name="date_admitted" type="date" value="{{ $admission->date_admitted }}">
            <input name="expected_leave_date" type="date" value="{{ $admission->expected_leave_date }}">
            <input name="actual_leave_date" type="date" value="{{ $admission->actual_leave_date }}">
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Admission</button></div>
        </form>
    </dialog>
@endforeach

<dialog id="appointment-modal">
    <form method="POST" action="{{ route('appointments.store') }}" class="modal-form" data-confirm="Add this appointment?">@csrf
        <h2>Schedule Appointment</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="consultant_id" required>@foreach ($consultants as $consultant)<option value="{{ $consultant->staff_id }}">{{ $consultant->first_name }} {{ $consultant->last_name }}</option>@endforeach</select>
        <input name="appointment_date" type="date" required><input name="appointment_time" type="time" required>
        <input name="room" placeholder="Room" required>
        <select name="recommendation"><option value="">Pending recommendation</option><option value="Out-Patient Clinic">Out-Patient Clinic</option><option value="Waiting List">Waiting List</option><option value="In-Patient Admission">In-Patient Admission</option></select>
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Appointment</button></div>
    </form>
</dialog>

@foreach ($appointments as $appointment)
    <dialog id="appointment-edit-{{ $appointment->appointment_id }}">
        <form method="POST" action="{{ route('appointments.update', $appointment->appointment_id) }}" class="modal-form" data-confirm="Save appointment changes?">
            @csrf
            @method('PUT')
            <h2>Edit Appointment</h2>
            <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}" @selected(($appointment->patient_id ?? null) === $patient->patient_id)>{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
            <select name="consultant_id" required>@foreach ($consultants as $consultant)<option value="{{ $consultant->staff_id }}" @selected(($appointment->consultant_id ?? null) === $consultant->staff_id)>{{ $consultant->first_name }} {{ $consultant->last_name }}</option>@endforeach</select>
            <input name="appointment_date" type="date" value="{{ $appointment->appointment_date ?? '' }}" required><input name="appointment_time" type="time" value="{{ substr($appointment->appointment_time ?? '', 0, 5) }}" required>
            <input name="room" value="{{ $appointment->room ?? '' }}" placeholder="Room" required>
            <select name="recommendation"><option value="">Pending recommendation</option><option value="Out-Patient Clinic" @selected(($appointment->recommendation ?? '') === 'Out-Patient Clinic')>Out-Patient Clinic</option><option value="Waiting List" @selected(($appointment->recommendation ?? '') === 'Waiting List')>Waiting List</option><option value="In-Patient Admission" @selected(($appointment->recommendation ?? '') === 'In-Patient Admission')>In-Patient Admission</option></select>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Appointment</button></div>
        </form>
    </dialog>
@endforeach

<dialog id="medication-modal">
    <form method="POST" action="{{ route('medications.store') }}" class="modal-form" data-confirm="Add this medication record?">@csrf
        <h2>Prescribe Medication</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="drug_id" required>@foreach ($drugs as $drug)<option value="{{ $drug->drug_id }}">{{ $drug->name }}</option>@endforeach</select>
        <input name="units_per_day" type="number" min="1" placeholder="Units per day" required>
        <input name="method_of_admin" maxlength="80" placeholder="Method of administration">
        <input name="start_date" type="date" required><input name="end_date" type="date">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Medication</button></div>
    </form>
</dialog>

@foreach ($medications as $medication)
    <dialog id="medication-edit-{{ $medication->medication_id }}">
        <form method="POST" action="{{ route('medications.update', $medication->medication_id) }}" class="modal-form" data-confirm="Save medication changes?">
            @csrf
            @method('PUT')
            <h2>Edit Medication</h2>
            <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}" @selected(($medication->patient_id ?? null) === $patient->patient_id)>{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
            <select name="drug_id" required>@foreach ($drugs as $drug)<option value="{{ $drug->drug_id }}" @selected(($medication->drug_id ?? null) === $drug->drug_id)>{{ $drug->name }}</option>@endforeach</select>
            <input name="units_per_day" type="number" min="1" value="{{ $medication->units_per_day ?? '' }}" placeholder="Units per day" required>
            <input name="method_of_admin" maxlength="80" value="{{ $medication->method_of_admin ?? '' }}" placeholder="Method of administration">
            <input name="start_date" type="date" value="{{ $medication->start_date ?? '' }}" required><input name="end_date" type="date" value="{{ $medication->end_date ?? '' }}">
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Medication</button></div>
        </form>
    </dialog>
@endforeach

<datalist id="contract-type-options">
    <option value="Permanent">
    <option value="Temporary">
    <option value="Fixed Term">
    <option value="Casual">
</datalist>

<datalist id="payment-type-options">
    <option value="Monthly">
    <option value="Weekly">
    <option value="Hourly">
</datalist>

<datalist id="qualification-type-options">
    <option value="Bachelor of Science in Nursing">
    <option value="Doctor of Medicine">
    <option value="Registered Nurse">
    <option value="Consultant Certification">
    <option value="Diploma in Nursing">
</datalist>

<datalist id="institution-options">
    <option value="University of Edinburgh">
    <option value="Harvard Medical School">
    <option value="Johns Hopkins University">
    <option value="University of Manchester">
</datalist>

<datalist id="ward-role-options">
    <option value="Ward Manager">
    <option value="Charge Nurse">
    <option value="Staff Nurse">
    <option value="Consultant">
    <option value="Doctor">
</datalist>

<datalist id="shift-options">
    <option value="Early">
    <option value="Late">
    <option value="Night">
    <option value="Day">
</datalist>
