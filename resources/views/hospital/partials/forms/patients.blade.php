<dialog id="patient-modal">
    <form method="POST" action="{{ route('patients.store') }}" class="modal-form" data-confirm="Add this patient record to Wellmeadows?">@csrf
        <h2>Register Patient</h2>
        <h3>Patient Information</h3>
        <input name="first_name" maxlength="50" placeholder="First name" required><input name="last_name" maxlength="50" placeholder="Last name" required>
        <textarea name="address" placeholder="Address" required></textarea><input name="phone" maxlength="20" placeholder="Telephone">
        <label class="date-field">Date of birth<input name="date_of_birth" type="date" required></label><select name="sex" required><option value="Female">Female</option><option value="Male">Male</option></select>
        <select name="marital_status"><option value="">Marital status</option><option value="Single">Single</option><option value="Married">Married</option><option value="Widowed">Widowed</option><option value="Divorced">Divorced</option></select>
        <h3>Next Of Kin</h3>
        <input name="kin_full_name" maxlength="100" placeholder="Next of kin full name"><input name="kin_relationship" maxlength="50" placeholder="Relationship">
        <textarea name="kin_address" placeholder="Next of kin address"></textarea><input name="kin_phone" maxlength="20" placeholder="Next of kin phone">
        <h3>Local Doctor And Referral</h3>
        <input type="text" list="local-doctor-options" data-local-doctor-input placeholder="Type or select local doctor">
        <input name="doctor_full_name" maxlength="100" placeholder="Local doctor full name"><input name="clinic_number" maxlength="50" placeholder="Clinic number">
        <textarea name="doctor_address" placeholder="Doctor clinic address"></textarea><input name="doctor_phone" maxlength="20" placeholder="Doctor phone">
        <label class="date-field">Referral date<input name="referral_date" type="date"></label>
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
            <label class="date-field">Date of birth<input name="date_of_birth" type="date" value="{{ $patient->date_of_birth }}" required></label><select name="sex" required><option value="Female" @selected($patient->sex === 'Female')>Female</option><option value="Male" @selected($patient->sex === 'Male')>Male</option></select>
            <select name="marital_status"><option value="">Marital status</option><option value="Single" @selected(($patient->marital_status ?? '') === 'Single')>Single</option><option value="Married" @selected(($patient->marital_status ?? '') === 'Married')>Married</option><option value="Widowed" @selected(($patient->marital_status ?? '') === 'Widowed')>Widowed</option><option value="Divorced" @selected(($patient->marital_status ?? '') === 'Divorced')>Divorced</option></select>
            <h3>Next Of Kin</h3>
            <input name="kin_full_name" maxlength="100" value="{{ $patient->kin_name ?? '' }}" placeholder="Next of kin full name"><input name="kin_relationship" maxlength="50" value="{{ $patient->kin_relationship ?? '' }}" placeholder="Relationship">
            <textarea name="kin_address" placeholder="Next of kin address">{{ $patient->kin_address ?? '' }}</textarea><input name="kin_phone" maxlength="20" value="{{ $patient->kin_phone ?? '' }}" placeholder="Next of kin phone">
            <h3>Local Doctor And Referral</h3>
            <input type="text" list="local-doctor-options" data-local-doctor-input value="{{ ($patient->doctor_name ?? null) ? ($patient->doctor_name.' - '.($patient->clinic_number ?? '')) : '' }}" placeholder="Type or select local doctor">
            <input name="doctor_full_name" maxlength="100" value="{{ $patient->doctor_name ?? '' }}" placeholder="Local doctor full name"><input name="clinic_number" maxlength="50" value="{{ $patient->clinic_number ?? '' }}" placeholder="Clinic number">
            <textarea name="doctor_address" placeholder="Doctor clinic address">{{ $patient->doctor_address ?? '' }}</textarea><input name="doctor_phone" maxlength="20" value="{{ $patient->doctor_phone ?? '' }}" placeholder="Doctor phone">
            <label class="date-field">Referral date<input name="referral_date" type="date" value="{{ $patient->referral_date ?? '' }}"></label>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Patient</button></div>
        </form>
    </dialog>
@endforeach
