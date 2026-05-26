<dialog id="medication-modal">
    <form method="POST" action="{{ route('medications.store') }}" class="modal-form" data-confirm="Add this medication record?">@csrf
        <h2>Prescribe Medication</h2>
        <select name="patient_id" required>@foreach ($patients->unique('patient_id') as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->first_name }} {{ $patient->last_name }}</option>@endforeach</select>
        <select name="drug_id" required>@foreach ($drugs as $drug)<option value="{{ $drug->drug_id }}">{{ $drug->name }}</option>@endforeach</select>
        <input name="units_per_day" type="number" min="1" placeholder="Units per day" required>
        <input name="method_of_admin" maxlength="80" placeholder="Method of administration">
        <label class="date-field">Start date<input name="start_date" type="date" required></label><label class="date-field">End date<input name="end_date" type="date"></label>
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
            <label class="date-field">Start date<input name="start_date" type="date" value="{{ $medication->start_date ?? '' }}" required></label><label class="date-field">End date<input name="end_date" type="date" value="{{ $medication->end_date ?? '' }}"></label>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Medication</button></div>
        </form>
    </dialog>
@endforeach
