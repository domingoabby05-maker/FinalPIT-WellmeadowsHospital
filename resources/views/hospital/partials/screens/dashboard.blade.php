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
                            <a href="#appointments" class="module-card"><span class="tile orange">A</span><strong>Appointments</strong><small>Schedule appointments and report out-patients</small><em>{{ $stats['today_appointments'] }} today</em></a>
                            <a href="#wards" class="module-card"><span class="tile violet">W</span><strong>Wards & Beds</strong><small>Manage waiting lists, inpatient admissions, beds, and wards</small><em>{{ $stats['wards'] }} wards</em></a>
                            <a href="#medications" class="module-card"><span class="tile red">M</span><strong>Medication Tracking</strong><small>Record and monitor patient medications</small><em>{{ $stats['active_prescriptions'] }} active</em></a>
                            <a href="#staff" class="module-card"><span class="tile green">S</span><strong>Staff & Administration</strong><small>Manage staff records, roles, rota, and account requests</small><em>{{ $stats['staff'] }} staff members</em></a>
                        </div>
                    </div>
                    <div>
                        <h2>Recent Activity</h2>
                        <div class="activity">
                            @php
                                $updatedRecord = collect()
                                    ->merge($patients->map(fn ($record) => ['title' => 'Patient record updated', 'detail' => trim(($record->first_name ?? '').' '.($record->last_name ?? '')), 'updated_at' => $record->updated_at ?? null]))
                                    ->merge($staff->map(fn ($record) => ['title' => 'Staff record updated', 'detail' => trim(($record->first_name ?? '').' '.($record->last_name ?? '')), 'updated_at' => $record->updated_at ?? null]))
                                    ->merge($appointments->map(fn ($record) => ['title' => 'Appointment updated', 'detail' => trim(($record->patient_first_name ?? '').' '.($record->patient_last_name ?? '')), 'updated_at' => $record->updated_at ?? null]))
                                    ->merge($medications->map(fn ($record) => ['title' => 'Medication record updated', 'detail' => trim(($record->drug_name ?? 'Medication').' for '.($record->first_name ?? 'patient')), 'updated_at' => $record->updated_at ?? null]))
                                    ->filter(fn ($record) => filled($record['updated_at']))
                                    ->sortByDesc('updated_at')
                                    ->first();
                            @endphp
                            @if ($updatedRecord)
                                <article><strong>{{ $updatedRecord['title'] }}</strong><span>{{ $updatedRecord['detail'] ?: 'Record details updated' }}</span><small>{{ $updatedRecord['updated_at'] }}</small></article>
                            @else
                                <article><strong>Dashboard updated</strong><span>Hospital records are ready for review</span><small>{{ now()->format('M j, Y g:i A') }}</small></article>
                            @endif
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
                        @if (($currentUser->role ?? null) === 'Medical Director')
                            <div class="activity dashboard-requests">
                                <div class="activity-head"><h2>Account Requests</h2><small>{{ $accountRequests->count() }} pending</small></div>
                                @forelse ($accountRequests->take(3) as $accountRequest)
                                    <article>
                                        <strong>{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}</strong>
                                        <span>{{ $accountRequest->email }} - {{ $accountRequest->role }}</span>
                                        <small>{{ $accountRequest->created_at }}</small>
                                        <div class="mini-actions">
                                            <form class="inline-form" method="POST" action="{{ route('account-requests.approve', $accountRequest->request_id) }}" data-confirm="Approve this account request and create a staff login?">
                                                @csrf
                                                <button class="icon" type="submit">Approve</button>
                                            </form>
                                            <form class="inline-form" method="POST" action="{{ route('account-requests.reject', $accountRequest->request_id) }}" data-confirm="Reject this account request?">
                                                @csrf
                                                <button class="icon danger-link" type="submit">Reject</button>
                                            </form>
                                        </div>
                                    </article>
                                @empty
                                    <article><strong>No pending account requests</strong><span>New account requests will appear here.</span></article>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </section>
