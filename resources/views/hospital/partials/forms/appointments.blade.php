<dialog id="appointment-modal">
    <form method="POST" action="{{ route('appointments.store') }}" class="modal-form" data-confirm="Add this appointment?">@csrf
        <h2>Schedule Appointment</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="consultant_id" required>@foreach ($consultants as $consultant)<option value="{{ $consultant->staff_id }}">{{ $consultant->first_name }} {{ $consultant->last_name }}</option>@endforeach</select>
        <label class="date-field">Appointment date<input name="appointment_date" type="date" min="{{ now()->toDateString() }}" required></label><input name="appointment_time" type="time" required>
        <input name="room" placeholder="Room" required>
        <select name="recommendation"><option value="">Pending recommendation</option><option value="Out-Patient Clinic">Out-Patient Clinic</option><option value="Waiting List">Waiting List</option><option value="In-Patient Admission">In-Patient Admission</option></select>
        <select name="admission_ward_id" data-ward-select><option value="">Admission ward</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">Ward {{ $ward->ward_id }} - {{ $ward->ward_name }}</option>@endforeach</select>
        <select name="admission_bed_id" data-bed-select><option value="">Waiting list - no bed assigned</option>@foreach ($beds as $bed)@if ($bed->bed_id ?? null)<option value="{{ $bed->bed_id }}" data-ward-id="{{ $bed->ward_id ?? '' }}">Bed {{ $bed->bed_number ?? 'N/A' }} - Ward {{ $bed->ward_id ?? 'N/A' }} - {{ $bed->status ?? 'Available' }}</option>@endif @endforeach</select>
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
            <label class="date-field">Appointment date<input name="appointment_date" type="date" value="{{ $appointment->appointment_date ?? '' }}" min="{{ now()->toDateString() }}" required></label><input name="appointment_time" type="time" value="{{ substr($appointment->appointment_time ?? '', 0, 5) }}" required>
            <input name="room" value="{{ $appointment->room ?? '' }}" placeholder="Room" required>
            <select name="recommendation"><option value="">Pending recommendation</option><option value="Out-Patient Clinic" @selected(($appointment->recommendation ?? '') === 'Out-Patient Clinic')>Out-Patient Clinic</option><option value="Waiting List" @selected(($appointment->recommendation ?? '') === 'Waiting List')>Waiting List</option><option value="In-Patient Admission" @selected(($appointment->recommendation ?? '') === 'In-Patient Admission')>In-Patient Admission</option></select>
            <select name="admission_ward_id" data-ward-select><option value="">Admission ward</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">Ward {{ $ward->ward_id }} - {{ $ward->ward_name }}</option>@endforeach</select>
            <select name="admission_bed_id" data-bed-select><option value="">Waiting list - no bed assigned</option>@foreach ($beds as $bed)@if ($bed->bed_id ?? null)<option value="{{ $bed->bed_id }}" data-ward-id="{{ $bed->ward_id ?? '' }}">Bed {{ $bed->bed_number ?? 'N/A' }} - Ward {{ $bed->ward_id ?? 'N/A' }} - {{ $bed->status ?? 'Available' }}</option>@endif @endforeach</select>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Appointment</button></div>
        </form>
    </dialog>
@endforeach
