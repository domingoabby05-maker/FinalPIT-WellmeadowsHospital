<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wellmeadows Hospital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .theme-toggle {
            grid-column: 1 / -1;
            width: 100%;
            min-height: 42px;
            margin-top: 18px;
            padding: 8px 10px;
            border: 1px solid #dfe5ef;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f8fbff;
            color: #263247;
            font-weight: 800;
        }

        .profile-identity {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        .profile-identity strong,
        .profile-identity small {
            overflow-wrap: anywhere;
            line-height: 1.25;
        }

        .profile-email {
            font-size: 12px;
        }

        .profile-role {
            font-weight: 800;
        }

        .theme-toggle span {
            min-width: 54px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #eaf2ff;
            color: #1769f6;
            font-size: 12px;
            font-weight: 900;
            text-align: center;
        }

        body.hospital-shell.theme-dark {
            background: #202632;
            color: #fff;
        }

        .theme-dark,
        .theme-dark a,
        .theme-dark p,
        .theme-dark h1,
        .theme-dark h2,
        .theme-dark h3,
        .theme-dark strong,
        .theme-dark span,
        .theme-dark small,
        .theme-dark em,
        .theme-dark b,
        .theme-dark th,
        .theme-dark td,
        .theme-dark label,
        .theme-dark button {
            color: #fff;
        }

        .theme-dark .signin,
        .theme-dark .signin-panel,
        .theme-dark .sidebar,
        .theme-dark dialog,
        .theme-dark .metric,
        .theme-dark .module-card,
        .theme-dark .table-card,
        .theme-dark .list-card,
        .theme-dark .ward-card,
        .theme-dark .ward-detail,
        .theme-dark .staff-card,
        .theme-dark .activity {
            background: #2a3140;
            color: #fff;
        }

        .theme-dark .sidebar,
        .theme-dark .brand,
        .theme-dark .profile,
        .theme-dark th,
        .theme-dark td,
        .theme-dark .activity article,
        .theme-dark .ward-detail header,
        .theme-dark .list-card article {
            border-color: #465264;
        }

        .theme-dark .signin-panel p,
        .theme-dark .signin-panel small,
        .theme-dark .brand small,
        .theme-dark .profile small,
        .theme-dark td small,
        .theme-dark .module-card small,
        .theme-dark .module-card em,
        .theme-dark .activity span,
        .theme-dark .activity small,
        .theme-dark .activity-head small,
        .theme-dark .page-head p {
            color: #fff;
        }

        .theme-dark .page-head h1,
        .theme-dark th,
        .theme-dark .modal-form h3,
        .theme-dark .date-field {
            color: #fff;
        }

        .theme-dark input,
        .theme-dark textarea,
        .theme-dark select {
            border-color: #566276 !important;
            background: #242b38 !important;
            color: #fff !important;
        }

        .theme-dark .auth-tabs button,
        .theme-dark .ghost,
        .theme-dark .modal-form button[data-close],
        .theme-dark .theme-toggle {
            border-color: #566276;
            background: #252d3a;
            color: #fff;
        }

        .theme-dark .auth-tabs button.active,
        .theme-dark .sidebar a.active,
        .theme-dark .sidebar a:hover,
        .theme-dark .theme-toggle span {
            background: #365a7f;
            color: #fff;
        }
    </style>
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
    @include('hospital.partials.layout.signin')

    <main class="app">
        @include('hospital.partials.layout.sidebar')

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

            @include('hospital.partials.screens.dashboard')

            @include('hospital.partials.screens.patients')

            @include('hospital.partials.screens.appointments')

            @include('hospital.partials.screens.wards')

            @include('hospital.partials.screens.medications')

            @include('hospital.partials.screens.staff')
        </div>
    </main>

    @include('hospital.partials.forms')
    <script>
        (() => {
            const applyTheme = (theme) => {
                const isDark = theme === 'dark';
                document.body.classList.toggle('theme-dark', isDark);
                document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(isDark));
                });
                document.querySelectorAll('[data-theme-toggle-icon]').forEach((node) => {
                    node.textContent = isDark ? 'Night' : 'Light';
                });
                document.querySelectorAll('[data-theme-toggle-label]').forEach((node) => {
                    node.textContent = isDark ? 'Night Mode' : 'Light Mode';
                });
            };

            const savedTheme = localStorage.getItem('wellmeadows-theme') || 'light';
            applyTheme(savedTheme);

            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const nextTheme = document.body.classList.contains('theme-dark') ? 'light' : 'dark';
                    localStorage.setItem('wellmeadows-theme', nextTheme);
                    applyTheme(nextTheme);
                });
            });

            const updateLocalDoctorFields = (input) => {
                const form = input.closest('form');
                const selected = [...(input.list?.options || [])].find((option) => option.value === input.value);

                if (!form || !selected) {
                    return;
                }

                form.querySelector('[name="doctor_full_name"]').value = selected.dataset.name || '';
                form.querySelector('[name="clinic_number"]').value = selected.dataset.clinic || '';
                form.querySelector('[name="doctor_address"]').value = selected.dataset.address || '';
                form.querySelector('[name="doctor_phone"]').value = selected.dataset.phone || '';
            };

            document.querySelectorAll('[data-local-doctor-input]').forEach((input) => {
                input.addEventListener('change', () => updateLocalDoctorFields(input));
                input.addEventListener('input', () => updateLocalDoctorFields(input));
            });
        })();
    </script>
</body>
</html>
