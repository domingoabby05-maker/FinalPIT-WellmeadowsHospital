<dialog id="admission-modal">
    <form method="POST" action="{{ route('admissions.store') }}" class="modal-form" data-confirm="Add this ward admission or waiting-list record?">
        @csrf
        <h2>Add Ward Admission</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="ward_id" required>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">Ward {{ $ward->ward_id }} - {{ $ward->ward_name }}</option>@endforeach</select>
        <select name="bed_id" data-bed-select><option value="">Waiting list - no bed assigned</option>@foreach ($beds as $bed)@if ($bed->bed_id ?? null)<option value="{{ $bed->bed_id }}" data-ward-id="{{ $bed->ward_id ?? '' }}">Bed {{ $bed->bed_number ?? 'N/A' }} - Ward {{ $bed->ward_id ?? 'N/A' }} - {{ $bed->status ?? 'Available' }}</option>@endif @endforeach</select>
        <label class="date-field">Date placed on waiting list<input name="date_placed_on_waiting_list" type="date"></label>
        <input name="expected_stay_days" type="number" min="1" placeholder="Expected stay days">
        <label class="date-field">Date admitted<input name="date_admitted" type="date"></label>
        <label class="date-field">Expected leave date<input name="expected_leave_date" type="date"></label>
        <label class="date-field">Actual leave date<input name="actual_leave_date" type="date"></label>
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
            <label class="date-field">Date placed on waiting list<input name="date_placed_on_waiting_list" type="date" value="{{ $admission->date_placed_on_waiting_list }}"></label>
            <input name="expected_stay_days" type="number" min="1" value="{{ $admission->expected_stay_days }}" placeholder="Expected stay days">
            <label class="date-field">Date admitted<input name="date_admitted" type="date" value="{{ $admission->date_admitted }}"></label>
            <label class="date-field">Expected leave date<input name="expected_leave_date" type="date" value="{{ $admission->expected_leave_date }}"></label>
            <label class="date-field">Actual leave date<input name="actual_leave_date" type="date" value="{{ $admission->actual_leave_date }}"></label>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Admission</button></div>
        </form>
    </dialog>
@endforeach
