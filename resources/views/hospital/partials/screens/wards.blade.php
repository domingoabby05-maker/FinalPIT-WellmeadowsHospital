            <section class="screen" id="wards-screen">
                <header class="page-head"><div><h1>Wards & Beds</h1><p>Manage waiting lists, inpatient admissions, wards, and bed allocation</p></div>@if ($canAdmissions)<button class="primary" data-modal="admission-modal">+ Add Admission</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Total Wards & Beds" :value="$stats['wards']" tone="blue" />
                    <x-metric label="Total Beds" :value="$stats['total_beds']" :sub="$stats['occupied_beds'].' occupied'" tone="green" />
                    <x-metric label="Occupancy Rate" :value="$stats['occupancy'].'%'" tone="violet" />
                </div>
                <div class="toolbar search-toolbar">
                    <input data-card-search="ward-grid" data-results-target="ward-search-results" data-empty-target="ward-empty-state" data-result-label-singular="ward" data-result-label-plural="wards" placeholder="Search ward or bed by ID, name, location, extension, staff, or patient">
                    <button class="primary" type="button" data-search-button="ward-grid">Search</button>
                    <small id="ward-search-results">{{ $wards->count() }} wards shown</small>
                </div>
                <div class="ward-grid">
                    @foreach ($wards as $ward)
                        @php
                            $occupied = (int) ($ward->occupied_beds ?? 0);
                            $capacity = max((int) $ward->total_beds, 1);
                            $rate = min(100, round(($occupied / $capacity) * 100));
                            $searchable = collect([
                                'W'.$ward->ward_id,
                                'Ward '.$ward->ward_id,
                                $ward->ward_id,
                                $ward->ward_name,
                                $ward->location,
                                $ward->tel_extension,
                                ($ward->assigned_staff ?? collect())->map(fn ($member) => trim(($member->first_name ?? '').' '.($member->last_name ?? '').' '.($member->position ?? '')))->implode(' '),
                                ($ward->ward_admissions ?? collect())->map(fn ($admission) => trim(($admission->first_name ?? '').' '.($admission->last_name ?? '').' '.($admission->patient_id ?? '')))->implode(' '),
                            ])->filter()->implode(' ');
                        @endphp
                        <a class="ward-card" href="#ward-detail-{{ $ward->ward_id }}" data-ward-card="{{ $ward->ward_id }}" data-searchable="{{ strtolower($searchable) }}">
                            <div class="ward-top"><h3>Ward {{ $ward->ward_id }}</h3><small>{{ $ward->ward_name }}</small><span>{{ $ward->location }}</span></div>
                            <div class="ward-meta"><span>Lead Staff<strong>{{ optional(($ward->assigned_staff ?? collect())->first())->first_name ?? 'Unassigned' }} {{ optional(($ward->assigned_staff ?? collect())->first())->last_name ?? '' }}</strong></span><span>Extension<strong>{{ $ward->tel_extension }}</strong></span></div>
                            <div class="progress"><label>Bed Occupancy <strong data-ward-occupancy="{{ $ward->ward_id }}">{{ $occupied }} / {{ $capacity }}</strong></label><i data-ward-progress="{{ $ward->ward_id }}" style="width: {{ $rate }}%"></i></div>
                            <small><span data-ward-available="{{ $ward->ward_id }}">{{ max($capacity - $occupied, 0) }}</span> beds available</small>
                            <div class="ward-foot"><span>Patients<strong data-ward-patients="{{ $ward->ward_id }}">{{ $occupied }}</strong></span><span>Staff<strong data-ward-staff="{{ $ward->ward_id }}">{{ $ward->staff_count ?? 0 }}</strong></span></div>
                        </a>
                    @endforeach
                    <div class="empty-state" id="ward-empty-state" hidden>No wards match your search.</div>
                </div>
                <div class="ward-detail-list">
                    @foreach ($wards as $ward)
                        <section class="ward-detail" id="ward-detail-{{ $ward->ward_id }}" data-ward-detail="{{ $ward->ward_id }}">
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
