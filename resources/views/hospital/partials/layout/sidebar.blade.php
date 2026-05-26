        <aside class="sidebar">
            <div class="brand">
                <span class="brand-mark">+</span>
                <span><strong>Wellmeadows</strong><small>Hospital System</small></span>
            </div>
            <nav>
                <a href="#dashboard" class="active" data-nav="dashboard">Dashboard</a>
                <a href="#patients" data-nav="patients">Patients</a>
                <a href="#appointments" data-nav="appointments">Appointments</a>
                <a href="#wards" data-nav="wards">Wards & Beds</a>
                <a href="#medications" data-nav="medications">Medications</a>
                <a href="#staff" data-nav="staff">Staff & Admin</a>
            </nav>
            <div class="profile">
                <span>{{ $initials }}</span>
                <div class="profile-identity">
                    <strong>{{ $currentUser->name ?? 'Wellmeadows User' }}</strong>
                    <small class="profile-role">{{ $currentUser->role ?? 'Medical Director' }}</small>
                    <small class="profile-email">{{ $currentUser->email ?? 'No email address' }}</small>
                </div>
                <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                    <span data-theme-toggle-icon>Light</span>
                    <strong data-theme-toggle-label>Light Mode</strong>
                </button>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Are you sure you want to logout?">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </aside>
