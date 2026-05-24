<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wellmeadows Hospital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $currentUser = auth()->user();
    $initials = $currentUser ? collect(explode(' ', $currentUser->name))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') : 'WM';
    $canPatients = $permissions['patients'] ?? false;
    $canStaff = $permissions['staff'] ?? false;
    $canAdmissions = $permissions['admissions'] ?? false;
    $canAppointments = $permissions['appointments'] ?? false;
    $canMedications = $permissions['medications'] ?? false;
@endphp
<body class="hospital-shell {{ $currentUser ? 'app-ready' : '' }}" data-authenticated="{{ $currentUser ? 'true' : 'false' }}" data-dashboard-live-url="{{ route('dashboard.live') }}">
    <section class="signin" id="signin">
        <div class="signin-hero">
            <div class="brand brand-light">
                <span class="brand-mark">+</span>
                <span><strong>Wellmeadows Hospital</strong><small>Edinburgh Healthcare System</small></span>
            </div>
            <div>
                <h1>Welcome to Wellmeadows Hospital</h1>
                <p>Organized records for patients, staff, wards, appointments, and medications.</p>
                <p>The system supports role-based access for hospital modules.</p>
            </div>
            <small>&copy; 2026 Wellmeadows Hospital. All rights reserved.</small>
        </div>
        <div class="signin-panel">
            <span class="signin-icon">+</span>
            <h2>Sign In</h2>
            <p>Choose a login method or create a new account.</p>
            <div class="auth-tabs">
                <button type="button" class="active" data-auth-tab="email">Email</button>
                <button type="button" data-auth-tab="hospital_id">Hospital ID</button>
                <button type="button" data-auth-tab="register">Create Account</button>
            </div>

            @if (session('status') || session('auth_status'))
                <div class="notice">{{ session('status') ?? session('auth_status') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form data-auth-form="email" class="auth-form active" method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="method" value="email">
                <label>Email Address<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Password<input type="password" name="password" required></label>
                <div class="form-row">
                    <label class="check"><input type="checkbox" name="remember"> Remember me</label>
                    <button class="primary" type="submit">Sign In</button>
                </div>
                <button class="link" type="submit" form="forgot-password-form">Forgot password?</button>
            </form>

            <form data-auth-form="hospital_id" class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="method" value="hospital_id">
                <label>Role<select name="role" required>
                    <option value="">Select role</option>
                    <option value="Medical Director" {{ old('role') === 'Medical Director' ? 'selected' : '' }}>Medical Director</option>
                    <option value="Doctor" {{ old('role') === 'Doctor' ? 'selected' : '' }}>Doctor</option>
                    <option value="Consultant" {{ old('role') === 'Consultant' ? 'selected' : '' }}>Consultant</option>
                    <option value="Charge Nurse" {{ old('role') === 'Charge Nurse' ? 'selected' : '' }}>Charge Nurse</option>
                    <option value="Nurse" {{ old('role') === 'Nurse' ? 'selected' : '' }}>Nurse</option>
                </select></label>
                <label>Hospital ID<input type="text" name="hospital_id" value="{{ old('hospital_id') }}" required></label>
                <label>Password<input type="password" name="password" required></label>
                <div class="form-row">
                    <button class="primary" type="submit">Sign In with Hospital ID</button>
                </div>
                <button class="link" type="submit" form="forgot-password-form">Forgot password?</button>
            </form>

            <form data-auth-form="register" class="auth-form" method="POST" action="{{ route('register') }}">
                @csrf
                <label>First Name<input type="text" name="first_name" value="{{ old('first_name') }}" required></label>
                <label>Last Name<input type="text" name="last_name" value="{{ old('last_name') }}" required></label>
                <label>Email Address<input type="email" name="email" value="{{ old('email') }}" required></label>
                <label>Role<select name="role" required>
                    <option value="">Select role</option>
                    <option value="Medical Director" {{ old('role') === 'Medical Director' ? 'selected' : '' }}>Medical Director</option>
                    <option value="Doctor" {{ old('role') === 'Doctor' ? 'selected' : '' }}>Doctor</option>
                    <option value="Consultant" {{ old('role') === 'Consultant' ? 'selected' : '' }}>Consultant</option>
                    <option value="Charge Nurse" {{ old('role') === 'Charge Nurse' ? 'selected' : '' }}>Charge Nurse</option>
                    <option value="Nurse" {{ old('role') === 'Nurse' ? 'selected' : '' }}>Nurse</option>
                </select></label>
                <label>Password<input type="password" name="password" required></label>
                <label>Confirm Password<input type="password" name="password_confirmation" required></label>
                <div class="form-row">
                    <button class="primary" type="submit">Request Account</button>
                </div>
                <small>A Medical Director must approve your request before your Hospital ID is active.</small>
            </form>

            <form id="forgot-password-form" method="POST" action="{{ route('password.forgot') }}" class="hidden">
                @csrf
            </form>
        </div>
    </section>

    <main class="app">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-mark">+</span>
                <span><strong>Wellmeadows</strong><small>Hospital System</small></span>
            </div>
            <nav>
                <a href="#dashboard" class="active" data-nav="dashboard">Dashboard</a>
                <a href="#patients" data-nav="patients">Patients</a>
                <a href="#staff" data-nav="staff">Staff</a>
                <a href="#wards" data-nav="wards">Wards</a>
                <a href="#appointments" data-nav="appointments">Appointments</a>
                <a href="#medications" data-nav="medications">Medications</a>
            </nav>
            <div class="profile">
                <span>{{ $initials }}</span>
                <strong>{{ $currentUser->name ?? 'Wellmeadows User' }}</strong>
                <small>{{ $currentUser->role ?? 'Medical Director' }}</small>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </aside>

        <div class="content">
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            @if ($offline)
                <div class="notice warning">Database connection is unavailable, so sample Wellmeadows data is being shown.</div>
                @if (! empty($databaseError))
                    <div class="notice error">{{ $databaseError }}</div>
                @endif
            @endif

            <section class="screen active" id="dashboard-screen">
                <header class="page-head">
                    <div><h1>Welcome, {{ $currentUser->name ?? 'Wellmeadows User' }}</h1><p>{{ $currentUser->role ?? 'Medical Director' }} - {{ $currentUser->hospital_id ?? 'Staff Account' }}</p></div>
                </header>
                <div class="metric-grid">
                    <x-metric label="Total Patients" :value="$stats['patients']" tone="blue" />
                    <x-metric label="Occupied Beds" :value="$stats['occupied_beds'].'/'.$stats['total_beds']" :sub="$stats['occupancy'].'%'" tone="green" />
                    <x-metric label="Today's Appointments" :value="$stats['today_appointments']" tone="violet" />
                    <x-metric label="Staff Records" :value="$stats['staff']" tone="orange" />
                </div>
                <div class="dashboard-grid">
                    <div>
                        <h2>Quick Access Modules</h2>
                        <div class="module-grid">
                            <a href="#patients" class="module-card"><span class="tile blue">P</span><strong>Patient Management</strong><small>Register, update and manage patient records</small><em>{{ $stats['patients'] }} patients</em></a>
                            <a href="#staff" class="module-card"><span class="tile green">S</span><strong>Staff Management</strong><small>Manage staff records, roles and schedules</small><em>{{ $stats['staff'] }} staff members</em></a>
                            <a href="#wards" class="module-card"><span class="tile violet">W</span><strong>Ward Management</strong><small>Monitor ward capacity and bed allocation</small><em>{{ $stats['wards'] }} wards</em></a>
                            <a href="#appointments" class="module-card"><span class="tile orange">A</span><strong>Appointments</strong><small>Schedule appointments and report out-patients</small><em>{{ $stats['today_appointments'] }} today</em></a>
                            <a href="#medications" class="module-card"><span class="tile red">M</span><strong>Medication Tracking</strong><small>Record and monitor patient medications</small><em>{{ $stats['active_prescriptions'] }} active</em></a>
                        </div>
                    </div>
                    <div>
                        <h2>Recent Activity</h2>
                        <div class="activity">
                            @foreach ($patients->take(2) as $patient)
                                <article><strong>Patient record active</strong><span>{{ $patient->first_name }} {{ $patient->last_name }}</span><small>{{ $patient->date_registered ?? 'Registered' }}</small></article>
                            @endforeach
                            @foreach ($appointments->take(2) as $appointment)
                                <article><strong>Appointment scheduled</strong><span>{{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}</span><small>{{ $appointment->appointment_date }} {{ substr($appointment->appointment_time, 0, 5) }}</small></article>
                            @endforeach
                            @foreach ($medications->take(1) as $medication)
                                <article><strong>Medication prescribed</strong><span>{{ $medication->drug_name }} for {{ $medication->first_name }}</span><small>{{ $medication->start_date }}</small></article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="screen" id="patients-screen">
                <header class="page-head"><div><h1>Patient Management</h1><p>Manage patient records and information</p></div>@if ($canPatients)<button class="primary" data-modal="patient-modal">+ Register Patient</button>@endif</header>
                <div class="toolbar"><input data-search="patients-table" placeholder="Search by name or patient ID"><select data-table-filter="patients-table" data-filter-column="status"><option value="">All Status</option><option value="in-patient">In-Patient</option><option value="out-patient">Out-Patient</option><option value="waiting-list">Waiting List</option></select></div>
                <div class="table-card">
                    <table id="patients-table">
                        <thead><tr><th>Patient ID</th><th>Name</th><th>Age/Sex</th><th>Contact</th><th>Next of Kin</th><th>Local Doctor</th><th>Status</th><th>Ward/Bed</th><th>Actions</th></tr></thead>
                        <tbody>
                        @foreach ($patients->unique('patient_id') as $patient)
                            @php
                                $age = $patient->date_of_birth ? \Illuminate\Support\Carbon::parse($patient->date_of_birth)->age : 'N/A';
                                $status = $patient->date_admitted ? 'In-Patient' : 'Waiting List';
                            @endphp
                            <tr data-status="{{ strtolower(str_replace(' ', '-', $status)) }}">
                                <td>{{ str_starts_with((string) $patient->patient_id, 'P') ? $patient->patient_id : 'P'.str_pad($patient->patient_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td><span class="avatar">{{ substr($patient->first_name, 0, 1) }}</span>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                                <td>{{ $age }} / {{ $patient->sex }}</td>
                                <td>{{ $patient->phone }}<small>{{ $patient->address }}</small></td>
                                <td>{{ $patient->kin_name ?? 'N/A' }}<small>{{ $patient->kin_relationship ?? 'No relationship listed' }}{{ ($patient->kin_phone ?? null) ? ' - '.$patient->kin_phone : '' }}</small></td>
                                <td>{{ $patient->doctor_name ?? 'N/A' }}<small>{{ $patient->clinic_number ?? 'No clinic number' }}{{ ($patient->referral_date ?? null) ? ' - '.$patient->referral_date : '' }}</small></td>
                                <td><span class="pill {{ $status === 'In-Patient' ? 'ok' : 'warn' }}">{{ $status }}</span></td>
                                <td>{{ $patient->ward_name ?? 'Pending' }}<small>Bed {{ $patient->bed_number ?? 'N/A' }}</small></td>
                                <td>
                                    @if ($canPatients)
                                    <button class="icon" type="button" data-modal="patient-edit-{{ $patient->patient_id }}">Edit</button>
                                    <form class="inline-form" method="POST" action="{{ route('patients.destroy', $patient->patient_id) }}" data-confirm="Delete patient record for {{ $patient->first_name }} {{ $patient->last_name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon danger-link" type="submit">Delete</button>
                                    </form>
                                    @else
                                        <span class="pill">View only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="screen" id="staff-screen">
                <header class="page-head"><div><h1>Staff Management</h1><p>Manage staff records, departments, roles, and schedules</p></div>@if ($canStaff)<button class="primary" data-modal="staff-modal">+ Add Staff</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Total Staff" :value="$stats['staff']" sub="Active records" tone="green" />
                    <x-metric label="Doctors" :value="$stats['doctors']" sub="Consultants included" tone="blue" />
                    <x-metric label="Nurses" :value="$stats['nurses']" sub="Ward coverage" tone="violet" />
                    <x-metric label="On Duty" value="95%" sub="Today" tone="orange" />
                </div>
                <div class="toolbar staff-toolbar">
                    <input data-card-search="staff-grid" data-results-target="staff-search-results" data-empty-target="staff-empty-state" placeholder="Search staff by name, ID, position, ward, phone, NIN, qualification, or experience">
                    <small id="staff-search-results">{{ $staff->count() }} staff records shown</small>
                </div>
                <div class="staff-grid">
                    @foreach ($staff as $member)
                        @php
                            $age = $member->date_of_birth ? \Illuminate\Support\Carbon::parse($member->date_of_birth)->age : 'N/A';
                            $searchable = collect([
                                $member->staff_id,
                                $member->first_name,
                                $member->last_name,
                                $member->position,
                                $member->ward_name,
                                $member->phone,
                                $member->nin,
                                $member->qualification_type ?? null,
                                $member->institution ?? null,
                                $member->experience_position ?? null,
                                $member->organization ?? null,
                            ])->filter()->implode(' ');
                        @endphp
                        <article class="staff-card" data-searchable="{{ strtolower($searchable) }}">
                            <div class="staff-top">
                                <h3>{{ $member->first_name }} {{ $member->last_name }}</h3>
                                <small>{{ $member->position }}</small>
                                <span>{{ str_starts_with((string) $member->staff_id, 'S') ? $member->staff_id : 'S'.str_pad($member->staff_id, 3, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="staff-meta">
                                <span>Phone<strong>{{ $member->phone ?? 'N/A' }}</strong></span>
                                <span>Ward<strong>{{ $member->ward_name ?? 'Administration' }}</strong></span>
                                <span>Sex / Age<strong>{{ $member->sex }} / {{ $age }}</strong></span>
                                <span>NIN<strong>{{ $member->nin }}</strong></span>
                            </div>
                            <div class="staff-detail">
                                <span>Address<strong>{{ $member->address }}</strong></span>
                                <span>Salary<strong>£{{ number_format((float) $member->salary, 2) }} / {{ $member->salary_scale ?? 'N/A' }}</strong></span>
                                <span>Contract<strong>{{ $member->contract_type ?? 'N/A' }}{{ $member->hours_per_week ? ' - '.$member->hours_per_week.' hrs/week' : '' }}</strong><small>{{ $member->payment_type ?? 'No payment type listed' }}</small></span>
                                <span>Qualification<strong>{{ $member->qualification_type ?? 'N/A' }}</strong><small>{{ $member->institution ?? 'No institution listed' }}{{ $member->date_obtained ? ' - '.$member->date_obtained : '' }}</small></span>
                                <span>Experience<strong>{{ $member->experience_position ?? 'N/A' }}</strong><small>{{ $member->organization ?? 'No organization listed' }}{{ $member->start_date ? ' - '.$member->start_date : '' }}{{ $member->end_date ? ' to '.$member->end_date : '' }}</small></span>
                            </div>
                            <div class="staff-actions">
                                @if ($canStaff)
                                <button class="ghost" type="button" data-modal="staff-edit-{{ $member->staff_id }}">Edit</button>
                                <form method="POST" action="{{ route('staff.destroy', $member->staff_id) }}" data-confirm="Delete staff record for {{ $member->first_name }} {{ $member->last_name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="danger" type="submit">Delete</button>
                                </form>
                                @else
                                    <span class="pill">View only</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                    <div class="empty-state" id="staff-empty-state" hidden>No staff records match your search.</div>
                </div>
                <div class="table-card">
                    @if ($canStaff)
                    <h2>Pending Account Requests</h2>
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Requested Role</th><th>Requested</th><th>Actions</th></tr></thead>
                        <tbody>
                        @forelse ($accountRequests as $accountRequest)
                            <tr>
                                <td>{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}</td>
                                <td>{{ $accountRequest->email }}</td>
                                <td>{{ $accountRequest->role }}</td>
                                <td>{{ $accountRequest->created_at }}</td>
                                <td>
                                    <form class="inline-form" method="POST" action="{{ route('account-requests.approve', $accountRequest->request_id) }}" data-confirm="Approve this account request and create a staff login?">
                                        @csrf
                                        <button class="icon" type="submit">Approve</button>
                                    </form>
                                    <form class="inline-form" method="POST" action="{{ route('account-requests.reject', $accountRequest->request_id) }}" data-confirm="Reject this account request?">
                                        @csrf
                                        <button class="icon danger-link" type="submit">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No pending account requests.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="table-card">
                    <h2>Weekly Staff Rota</h2>
                    <table>
                        <thead><tr><th>Week</th><th>Staff</th><th>Position</th><th>Ward/Clinic</th><th>Shift</th><th>Duty Area</th></tr></thead>
                        <tbody>
                        @forelse ($rotas as $rota)
                            <tr>
                                <td>{{ $rota->week_start_date }}</td>
                                <td>{{ $rota->first_name }} {{ $rota->last_name }}</td>
                                <td>{{ $rota->position }}</td>
                                <td>{{ $rota->ward_name ?? 'Out-Patient Clinic' }}</td>
                                <td><span class="pill ok">{{ $rota->shift }}</span></td>
                                <td>{{ $rota->duty_area ?? 'General duty' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No rota records have been added.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="screen" id="wards-screen">
                <header class="page-head"><div><h1>Ward Management</h1><p>Monitor ward capacity and bed allocation</p></div>@if ($canAdmissions)<button class="primary" data-modal="admission-modal">+ Add Admission</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Total Wards" :value="$stats['wards']" tone="blue" />
                    <x-metric label="Total Beds" :value="$stats['total_beds']" :sub="$stats['occupied_beds'].' occupied'" tone="green" />
                    <x-metric label="Occupancy Rate" :value="$stats['occupancy'].'%'" tone="violet" />
                </div>
                <div class="ward-grid">
                    @foreach ($wards as $ward)
                        @php
                            $occupied = (int) ($ward->occupied_beds ?? 0);
                            $capacity = max((int) $ward->total_beds, 1);
                            $rate = min(100, round(($occupied / $capacity) * 100));
                        @endphp
                        <a class="ward-card" href="#ward-detail-{{ $ward->ward_id }}" data-ward-card="{{ $ward->ward_id }}">
                            <div class="ward-top"><h3>Ward {{ $ward->ward_id }}</h3><small>{{ $ward->ward_name }}</small><span>{{ $ward->location }}</span></div>
                            <div class="ward-meta"><span>Lead Staff<strong>{{ optional(($ward->assigned_staff ?? collect())->first())->first_name ?? 'Unassigned' }} {{ optional(($ward->assigned_staff ?? collect())->first())->last_name ?? '' }}</strong></span><span>Extension<strong>{{ $ward->tel_extension }}</strong></span></div>
                            <div class="progress"><label>Bed Occupancy <strong data-ward-occupancy="{{ $ward->ward_id }}">{{ $occupied }} / {{ $capacity }}</strong></label><i data-ward-progress="{{ $ward->ward_id }}" style="width: {{ $rate }}%"></i></div>
                            <small><span data-ward-available="{{ $ward->ward_id }}">{{ max($capacity - $occupied, 0) }}</span> beds available</small>
                            <div class="ward-foot"><span>Patients<strong data-ward-patients="{{ $ward->ward_id }}">{{ $occupied }}</strong></span><span>Staff<strong data-ward-staff="{{ $ward->ward_id }}">{{ $ward->staff_count ?? 0 }}</strong></span></div>
                        </a>
                    @endforeach
                </div>
                <div class="ward-detail-list">
                    @foreach ($wards as $ward)
                        <section class="ward-detail" id="ward-detail-{{ $ward->ward_id }}">
                            <header>
                                <div>
                                    <h2>{{ $ward->ward_name }}</h2>
                                    <p>Ward {{ $ward->ward_id }} - {{ $ward->location }} - Extension {{ $ward->tel_extension }}</p>
                                </div>
                                <span>{{ (int) ($ward->occupied_beds ?? 0) }} / {{ (int) $ward->total_beds }} beds occupied</span>
                            </header>
                            <div class="ward-detail-grid">
                                <div>
                                    <h3>Assigned Staff</h3>
                                    <div class="mini-list">
                                        @forelse (($ward->assigned_staff ?? collect()) as $member)
                                            <article><strong>{{ $member->first_name }} {{ $member->last_name }}</strong><span>{{ $member->position }} - {{ $member->shift ?? 'No shift listed' }}</span><small>{{ $member->role ?? 'Staff' }}</small></article>
                                        @empty
                                            <article><strong>No staff assigned</strong><span>Assign staff through ward staffing records.</span></article>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <h3>Patients And Admissions</h3>
                                    <div class="mini-list">
                                        @forelse (($ward->ward_admissions ?? collect()) as $admission)
                                            <article class="{{ $admission->actual_leave_date ? 'is-discharged' : '' }}">
                                                <strong>{{ $admission->first_name }} {{ $admission->last_name }} <small>{{ str_starts_with((string) $admission->patient_id, 'P') ? $admission->patient_id : 'P'.str_pad($admission->patient_id, 5, '0', STR_PAD_LEFT) }}</small></strong>
                                                <span>Bed {{ $admission->bed_number ?? 'N/A' }} - Admitted {{ $admission->date_admitted ?? 'N/A' }}</span>
                                                <small>{{ $admission->actual_leave_date ? 'Out: '.$admission->actual_leave_date : 'Active admission' }}</small>
                                                @if ($canAdmissions)
                                                <div class="mini-actions">
                                                    <button class="icon" type="button" data-modal="admission-edit-{{ $admission->admission_id }}">Edit</button>
                                                    <form class="inline-form" method="POST" action="{{ route('admissions.destroy', $admission->admission_id) }}" data-confirm="Delete this ward admission?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="icon danger-link" type="submit">Delete</button>
                                                    </form>
                                                </div>
                                                @endif
                                            </article>
                                        @empty
                                            <article><strong>No patients assigned</strong><span>This ward has no admission records.</span></article>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>

            <section class="screen" id="appointments-screen">
                <header class="page-head"><div><h1>Appointments</h1><p>Schedule and manage patient appointments</p></div>@if ($canAppointments)<button class="primary" data-modal="appointment-modal">+ Schedule Appointment</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Today's Appointments" :value="$stats['today_appointments']" tone="blue" />
                    <x-metric label="Out-Patients" :value="$stats['outpatients']" tone="green" />
                    <x-metric label="Waiting List" :value="$admissions->whereNull('date_admitted')->count()" tone="orange" />
                    <x-metric label="Upcoming" :value="$appointments->count()" tone="violet" />
                </div>
                <div class="list-card">
                    @foreach ($appointments as $appointment)
                        <article><time>{{ substr($appointment->appointment_time ?? '', 0, 5) }}</time><strong>{{ $appointment->patient_first_name ?? '' }} {{ $appointment->patient_last_name ?? '' }} <small>{{ str_starts_with((string) ($appointment->patient_id ?? ''), 'P') ? $appointment->patient_id : 'P'.str_pad($appointment->patient_id ?? '', 5, '0', STR_PAD_LEFT) }}</small></strong><span>Dr. {{ $appointment->consultant_first_name ?? '' }} {{ $appointment->consultant_last_name ?? '' }}</span><em>{{ $appointment->room ?? 'N/A' }}</em><b>{{ $appointment->recommendation ?? 'Scheduled' }}</b>@if ($canAppointments && ($appointment->appointment_id ?? null))<span class="mini-actions"><button class="icon" type="button" data-modal="appointment-edit-{{ $appointment->appointment_id }}">Edit</button><form class="inline-form" method="POST" action="{{ route('appointments.destroy', $appointment->appointment_id) }}" data-confirm="Delete this appointment?">@csrf @method('DELETE')<button class="icon danger-link" type="submit">Delete</button></form></span>@endif</article>
                    @endforeach
                </div>
                <div class="table-card">
                    <h2>Out-Patient Clinic Report</h2>
                    <table>
                        <thead><tr><th>Patient No.</th><th>Name</th><th>Contact</th><th>Date of Birth/Sex</th><th>Appointment</th><th>Room</th></tr></thead>
                        <tbody>
                        @forelse ($outpatients as $outpatient)
                            <tr>
                                <td>{{ str_starts_with((string) $outpatient->patient_id, 'P') ? $outpatient->patient_id : 'P'.str_pad($outpatient->patient_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $outpatient->first_name }} {{ $outpatient->last_name }}</td>
                                <td>{{ $outpatient->phone }}<small>{{ $outpatient->address }}</small></td>
                                <td>{{ $outpatient->date_of_birth }}<small>{{ $outpatient->sex }}</small></td>
                                <td>{{ $outpatient->appointment_date }}<small>{{ substr($outpatient->appointment_time, 0, 5) }}</small></td>
                                <td>{{ $outpatient->room }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No out-patient records have been added.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="screen" id="medications-screen">
                <header class="page-head"><div><h1>Medication Tracking</h1><p>Record and monitor patient medications</p></div>@if ($canMedications)<button class="primary" data-modal="medication-modal">+ Prescribe Medication</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Active Prescriptions" :value="$stats['active_prescriptions']" tone="blue" />
                    <x-metric label="Medications Today" :value="$medications->count()" tone="green" />
                    <x-metric label="Drug Types" :value="$stats['drug_types']" tone="violet" />
                    <x-metric label="Expiring Soon" :value="$medications->where('end_date', '<=', now()->addDays(7)->toDateString())->count()" tone="orange" />
                </div>
                <div class="table-card">
                    <table><thead><tr><th>Patient</th><th>Ward/Bed</th><th>Medication</th><th>Dosage</th><th>Method</th><th>Units/Day</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                    @foreach ($medications as $medication)
                        <tr><td>{{ $medication->first_name }} {{ $medication->last_name }}<small>{{ str_starts_with((string) $medication->patient_id, 'P') ? $medication->patient_id : 'P'.str_pad($medication->patient_id, 5, '0', STR_PAD_LEFT) }}</small></td><td>{{ $medication->ward_name ?? 'Out-Patient Clinic' }}<small>Bed {{ $medication->bed_number ?? 'N/A' }}</small></td><td><strong>{{ $medication->drug_name }}</strong><small>{{ $medication->description }}</small></td><td>{{ $medication->dosage }}</td><td><span class="pill ok">{{ $medication->method_of_admin }}</span></td><td>{{ $medication->units_per_day }}</td><td>{{ $medication->start_date }}<small>{{ $medication->end_date ?? 'Ongoing' }}</small></td><td><span class="pill ok">Active</span></td><td>@if ($canMedications)<button class="icon" type="button" data-modal="medication-edit-{{ $medication->medication_id }}">Edit</button><form class="inline-form" method="POST" action="{{ route('medications.destroy', $medication->medication_id) }}" data-confirm="Delete this medication record?">@csrf @method('DELETE')<button class="icon danger-link" type="submit">Delete</button></form>@else<span class="pill">View only</span>@endif</td></tr>
                    @endforeach
                    </tbody></table>
                </div>
            </section>
        </div>
    </main>

    @include('hospital.partials.forms')
</body>
</html>
