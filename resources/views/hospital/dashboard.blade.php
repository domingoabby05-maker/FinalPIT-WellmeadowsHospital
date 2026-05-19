<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wellmeadows Hospital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hospital-shell">
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
        <form class="signin-panel" onsubmit="event.preventDefault(); document.body.classList.add('app-ready'); location.hash='dashboard';">
            <span class="signin-icon">+</span>
            <h2>Sign In</h2>
            <p>Sign in to your account</p>
            <label>Email Address<input type="email" value="director@wellmeadows.test"></label>
            <label>Password<input type="password" value="password"></label>
            <div class="form-row"><label class="check"><input type="checkbox"> Remember me</label><a>Forgot password?</a></div>
            <button class="ghost" type="button">Continue with Gmail</button>
            <button class="ghost" type="button">Continue with Microsoft</button>
            <button class="ghost" type="button">Continue with Hospital ID</button>
            <button class="primary" type="submit">Sign In</button>
            <small>Don't have an account? <strong>Create Account</strong></small>
        </form>
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
                <span>JS</span>
                <strong>John Smith</strong>
                <small>Medical Director</small>
                <button onclick="document.body.classList.remove('app-ready')">Logout</button>
            </div>
        </aside>

        <div class="content">
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            @if ($offline)
                <div class="notice warning">Database connection is unavailable, so sample Wellmeadows data is being shown.</div>
            @endif

            <section class="screen active" id="dashboard-screen">
                <header class="page-head">
                    <div><h1>Welcome, John Smith</h1><p>Medical Director - Staff #S001</p></div>
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
                            <a href="#appointments" class="module-card"><span class="tile orange">A</span><strong>Appointments</strong><small>Schedule and manage patient appointments</small><em>{{ $stats['today_appointments'] }} today</em></a>
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
                <header class="page-head"><div><h1>Patient Management</h1><p>Manage patient records and information</p></div><button class="primary" data-modal="patient-modal">+ Register Patient</button></header>
                <div class="toolbar"><input data-search="patients-table" placeholder="Search by name or patient ID"><select><option>All Status</option><option>In-Patient</option><option>Out-Patient</option></select></div>
                <div class="table-card">
                    <table id="patients-table">
                        <thead><tr><th>Patient ID</th><th>Name</th><th>Age/Sex</th><th>Contact</th><th>Status</th><th>Ward/Bed</th><th>Actions</th></tr></thead>
                        <tbody>
                        @foreach ($patients as $patient)
                            @php
                                $age = $patient->date_of_birth ? \Illuminate\Support\Carbon::parse($patient->date_of_birth)->age : 'N/A';
                                $status = $patient->date_admitted ? 'In-Patient' : 'Waiting List';
                            @endphp
                            <tr>
                                <td>P{{ str_pad($patient->patient_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td><span class="avatar">{{ substr($patient->first_name, 0, 1) }}</span>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                                <td>{{ $age }} / {{ $patient->sex }}</td>
                                <td>{{ $patient->phone }}<small>{{ $patient->address }}</small></td>
                                <td><span class="pill {{ $status === 'In-Patient' ? 'ok' : 'warn' }}">{{ $status }}</span></td>
                                <td>{{ $patient->ward_name ?? 'Pending' }}<small>Bed {{ $patient->bed_number ?? 'N/A' }}</small></td>
                                <td><button class="icon">View</button><button class="icon">Edit</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="screen" id="staff-screen">
                <header class="page-head"><div><h1>Staff Management</h1><p>Manage staff records, departments, roles, and schedules</p></div><button class="primary" data-modal="staff-modal">+ Add Staff</button></header>
                <div class="metric-grid">
                    <x-metric label="Total Staff" :value="$stats['staff']" sub="Active records" tone="green" />
                    <x-metric label="Doctors" :value="$stats['doctors']" sub="Consultants included" tone="blue" />
                    <x-metric label="Nurses" :value="$stats['nurses']" sub="Ward coverage" tone="violet" />
                    <x-metric label="On Duty" value="95%" sub="Today" tone="orange" />
                </div>
                <div class="table-card">
                    <table><thead><tr><th>Staff</th><th>Role</th><th>Department</th><th>Ward</th><th>Extension</th><th>Schedule</th></tr></thead><tbody>
                    @foreach ($staff as $member)
                        <tr><td>{{ $member->first_name }} {{ $member->last_name }}</td><td>{{ $member->staff_position }}</td><td>{{ $member->ward_name ?? 'Administration' }}</td><td>{{ $member->ward_name ? 'Ward' : 'N/A' }}</td><td>{{ $member->tel_extension ?? '7701' }}</td><td>{{ $member->shift ?? 'Mon-Fri' }}</td></tr>
                    @endforeach
                    </tbody></table>
                </div>
            </section>

            <section class="screen" id="wards-screen">
                <header class="page-head"><div><h1>Ward Management</h1><p>Monitor ward capacity and bed allocation</p></div></header>
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
                        <article class="ward-card">
                            <div class="ward-top"><h3>Ward {{ $ward->ward_id }}</h3><small>{{ $ward->ward_name }}</small><span>{{ $ward->location }}</span></div>
                            <div class="ward-meta"><span>Charge Nurse<strong>{{ optional($staff->firstWhere('ward_name', $ward->ward_name))->first_name ?? 'Moira' }} {{ optional($staff->firstWhere('ward_name', $ward->ward_name))->last_name ?? 'Samuel' }}</strong></span><span>Extension<strong>{{ $ward->tel_extension }}</strong></span></div>
                            <div class="progress"><label>Bed Occupancy <strong>{{ $occupied }} / {{ $capacity }}</strong></label><i style="width: {{ $rate }}%"></i></div>
                            <small>{{ max($capacity - $occupied, 0) }} beds available</small>
                            <div class="ward-foot"><span>Patients<strong>{{ $occupied }}</strong></span><span>Staff<strong>{{ $ward->staff_count ?? 0 }}</strong></span></div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="screen" id="appointments-screen">
                <header class="page-head"><div><h1>Appointments</h1><p>Schedule and manage patient appointments</p></div><button class="primary" data-modal="appointment-modal">+ Schedule Appointment</button></header>
                <div class="metric-grid">
                    <x-metric label="Today's Appointments" :value="$stats['today_appointments']" tone="blue" />
                    <x-metric label="Completed" value="8" tone="green" />
                    <x-metric label="In Progress" value="3" tone="orange" />
                    <x-metric label="Upcoming" :value="$appointments->count()" tone="violet" />
                </div>
                <div class="list-card">
                    @foreach ($appointments as $appointment)
                        <article><time>{{ substr($appointment->appointment_time, 0, 5) }}</time><strong>{{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }} <small>P{{ str_pad($appointment->patient_id, 5, '0', STR_PAD_LEFT) }}</small></strong><span>Dr. {{ $appointment->consultant_first_name }} {{ $appointment->consultant_last_name }}</span><em>{{ $appointment->room }}</em><b>Scheduled</b></article>
                    @endforeach
                </div>
            </section>

            <section class="screen" id="medications-screen">
                <header class="page-head"><div><h1>Medication Tracking</h1><p>Record and monitor patient medications</p></div><button class="primary" data-modal="medication-modal">+ Prescribe Medication</button></header>
                <div class="metric-grid">
                    <x-metric label="Active Prescriptions" :value="$stats['active_prescriptions']" tone="blue" />
                    <x-metric label="Medications Today" :value="$medications->count()" tone="green" />
                    <x-metric label="Drug Types" :value="$stats['drug_types']" tone="violet" />
                    <x-metric label="Expiring Soon" :value="$medications->where('end_date', '<=', now()->addDays(7)->toDateString())->count()" tone="orange" />
                </div>
                <div class="table-card">
                    <table><thead><tr><th>Patient</th><th>Ward/Bed</th><th>Medication</th><th>Dosage</th><th>Method</th><th>Units/Day</th><th>Duration</th><th>Status</th></tr></thead><tbody>
                    @foreach ($medications as $medication)
                        <tr><td>{{ $medication->first_name }} {{ $medication->last_name }}<small>P{{ str_pad($medication->patient_id, 5, '0', STR_PAD_LEFT) }}</small></td><td>{{ $medication->ward_name ?? 'Out-Patient Clinic' }}<small>Bed {{ $medication->bed_number ?? 'N/A' }}</small></td><td><strong>{{ $medication->drug_name }}</strong><small>{{ $medication->description }}</small></td><td>{{ $medication->dosage }}</td><td><span class="pill ok">{{ $medication->method_of_admin }}</span></td><td>{{ $medication->units_per_day }}</td><td>{{ $medication->start_date }}<small>{{ $medication->end_date ?? 'Ongoing' }}</small></td><td><span class="pill ok">Active</span></td></tr>
                    @endforeach
                    </tbody></table>
                </div>
            </section>
        </div>
    </main>

    @include('hospital.partials.forms')
</body>
</html>
