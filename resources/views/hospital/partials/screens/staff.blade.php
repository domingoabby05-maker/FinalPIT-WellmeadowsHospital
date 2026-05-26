            <section class="screen" id="staff-screen">
                <header class="page-head"><div><h1>Staff & Administration</h1><p>Manage staff records, roles, schedules, and account requests</p></div>@if ($canStaff)<button class="primary" data-modal="staff-modal">+ Add Staff</button>@endif</header>
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
