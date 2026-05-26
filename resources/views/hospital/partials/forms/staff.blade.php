<dialog id="staff-modal">
    <form method="POST" action="{{ route('staff.store') }}" class="modal-form" data-confirm="Add this staff record to Wellmeadows?">@csrf
        <h2>Add Staff</h2>
        <h3>Staff Information</h3>
        <input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required>
        <textarea name="address" placeholder="Address" required></textarea><input name="phone" placeholder="Telephone">
        <label class="date-field">Date of birth<input name="date_of_birth" type="date" required></label><select name="sex" required><option value="Female">Female</option><option value="Male">Male</option></select>
        <input name="nin" placeholder="National insurance number" required><input name="position" placeholder="Position" required>
        <input name="salary" type="number" step="0.01" placeholder="Current salary" required><input name="salary_scale" placeholder="Salary scale">
        <h3>Assigned Ward</h3>
        <select name="ward_id"><option value="">No ward assigned</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}">{{ $ward->ward_name }} - {{ $ward->location ?? 'No location' }}</option>@endforeach</select>
        <input name="ward_role" list="ward-role-options" placeholder="Ward role">
        <select name="shift">
            <option value="">No shift assigned</option>
            <option value="Early">Early (6am-2pm)</option>
            <option value="Late">Late (2pm-10pm)</option>
            <option value="Night">Night (10pm-6am)</option>
        </select>
        <h3>Contract</h3>
        <input name="hours_per_week" type="number" step="0.01" placeholder="Hours per week">
        <select name="contract_type">
            <option value="">No contract type</option>
            <option value="Permanent">Permanent</option>
            <option value="Temporary">Temporary</option>
        </select>
        <input name="payment_type" list="payment-type-options" placeholder="Payment type">
        <h3>Qualification</h3>
        <input name="qualification_type" list="qualification-type-options" placeholder="Qualification type"><input name="institution" list="institution-options" placeholder="Institution">
        <label class="date-field">Date obtained<input name="date_obtained" type="date"></label>
        <h3>Work Experience</h3>
        <input name="experience_position" placeholder="Experience position"><input name="organization" placeholder="Organization">
        <label class="date-field">Start date<input name="start_date" type="date"></label><label class="date-field">End date<input name="end_date" type="date"></label>
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
            <label class="date-field">Date of birth<input name="date_of_birth" type="date" value="{{ $member->date_of_birth }}" required></label><select name="sex" required><option value="Female" @selected($member->sex === 'Female')>Female</option><option value="Male" @selected($member->sex === 'Male')>Male</option></select>
            <input name="nin" value="{{ $member->nin }}" placeholder="National insurance number" required><input name="position" value="{{ $member->position ?? '' }}" placeholder="Position" required>
            <input name="salary" type="number" step="0.01" value="{{ $member->salary }}" placeholder="Current salary" required><input name="salary_scale" value="{{ $member->salary_scale }}" placeholder="Salary scale">
            <h3>Assigned Ward</h3>
            <select name="ward_id"><option value="">No ward assigned</option>@foreach ($wards as $ward)<option value="{{ $ward->ward_id }}" @selected(($member->ward_id ?? null) === $ward->ward_id)>{{ $ward->ward_name }} - {{ $ward->location ?? 'No location' }}</option>@endforeach</select>
            <input name="ward_role" list="ward-role-options" value="{{ $member->role ?? '' }}" placeholder="Ward role">
            <select name="shift">
                <option value="">No shift assigned</option>
                <option value="Early" @selected(($member->shift ?? '') === 'Early')>Early (6am-2pm)</option>
                <option value="Late" @selected(($member->shift ?? '') === 'Late')>Late (2pm-10pm)</option>
                <option value="Night" @selected(($member->shift ?? '') === 'Night')>Night (10pm-6am)</option>
            </select>
            <h3>Contract</h3>
            <input name="hours_per_week" type="number" step="0.01" value="{{ $member->hours_per_week ?? '' }}" placeholder="Hours per week">
            <select name="contract_type">
                <option value="">No contract type</option>
                <option value="Permanent" @selected(($member->contract_type ?? '') === 'Permanent')>Permanent</option>
                <option value="Temporary" @selected(($member->contract_type ?? '') === 'Temporary')>Temporary</option>
            </select>
            <input name="payment_type" list="payment-type-options" value="{{ $member->payment_type ?? '' }}" placeholder="Payment type">
            <h3>Qualification</h3>
            <input name="qualification_type" list="qualification-type-options" value="{{ $member->qualification_type ?? '' }}" placeholder="Qualification type"><input name="institution" list="institution-options" value="{{ $member->institution ?? '' }}" placeholder="Institution">
            <label class="date-field">Date obtained<input name="date_obtained" type="date" value="{{ $member->date_obtained ?? '' }}"></label>
            <h3>Work Experience</h3>
            <input name="experience_position" value="{{ $member->experience_position ?? '' }}" placeholder="Experience position"><input name="organization" value="{{ $member->organization ?? '' }}" placeholder="Organization">
            <label class="date-field">Start date<input name="start_date" type="date" value="{{ $member->start_date ?? '' }}"></label><label class="date-field">End date<input name="end_date" type="date" value="{{ $member->end_date ?? '' }}"></label>
            <div><button type="button" data-close>Cancel</button><button class="primary">Update Staff</button></div>
        </form>
    </dialog>
@endforeach
