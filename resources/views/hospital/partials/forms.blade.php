<dialog id="patient-modal">
    <form method="POST" action="{{ route('patients.store') }}" class="modal-form">@csrf
        <h2>Register Patient</h2>
        <input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required>
        <input name="address" placeholder="Address" required><input name="phone" placeholder="Telephone">
        <input name="date_of_birth" type="date" required><select name="sex" required><option value="F">Female</option><option value="M">Male</option></select>
        <input name="marital_status" placeholder="Marital status">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Patient</button></div>
    </form>
</dialog>

<dialog id="staff-modal">
    <form method="POST" action="{{ route('staff.store') }}" class="modal-form">@csrf
        <h2>Add Staff</h2>
        <input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required>
        <input name="address" placeholder="Address" required><input name="phone" placeholder="Telephone">
        <input name="date_of_birth" type="date" required><select name="sex" required><option value="F">Female</option><option value="M">Male</option></select>
        <input name="nin" placeholder="National insurance number" required><input name="staff_position" placeholder="Position" required>
        <input name="salary" type="number" step="0.01" placeholder="Current salary" required><input name="salary_scale" placeholder="Salary scale">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Staff</button></div>
    </form>
</dialog>

<dialog id="appointment-modal">
    <form method="POST" action="{{ route('appointments.store') }}" class="modal-form">@csrf
        <h2>Schedule Appointment</h2>
        <select name="patient_id" required>@foreach ($patients as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="consultant_id" required>@foreach ($consultants as $consultant)<option value="{{ $consultant->staff_id }}">{{ $consultant->first_name }} {{ $consultant->last_name }}</option>@endforeach</select>
        <input name="appointment_date" type="date" required><input name="appointment_time" type="time" required>
        <input name="room" placeholder="Room" required>
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Appointment</button></div>
    </form>
</dialog>

<dialog id="medication-modal">
    <form method="POST" action="{{ route('medications.store') }}" class="modal-form">@csrf
        <h2>Prescribe Medication</h2>
        <select name="patient_id" required>@foreach ($patients as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="drug_id" required>@foreach ($drugs as $drug)<option value="{{ $drug->drug_id }}">{{ $drug->name }}</option>@endforeach</select>
        <input name="units_per_day" type="number" min="1" placeholder="Units per day" required>
        <input name="start_date" type="date" required><input name="end_date" type="date">
        <div><button type="button" data-close>Cancel</button><button class="primary">Save Medication</button></div>
    </form>
</dialog>
