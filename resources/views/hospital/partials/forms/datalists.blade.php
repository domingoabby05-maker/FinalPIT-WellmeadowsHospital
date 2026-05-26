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

<datalist id="local-doctor-options">
    @foreach ($localDoctors as $doctor)
        <option value="{{ $doctor->full_name }} - {{ $doctor->clinic_number }}" data-name="{{ $doctor->full_name }}" data-clinic="{{ $doctor->clinic_number }}" data-address="{{ $doctor->address }}" data-phone="{{ $doctor->phone }}"></option>
    @endforeach
</datalist>
