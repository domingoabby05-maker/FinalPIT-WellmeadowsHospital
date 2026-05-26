            <section class="screen" id="appointments-screen">
                <header class="page-head"><div><h1>Appointments & Out-Patients</h1><p>Schedule appointments and record outpatient recommendations</p></div>@if ($canAppointments)<button class="primary" data-modal="appointment-modal">+ Schedule Appointment</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Today's Appointments" :value="$stats['today_appointments']" tone="blue" />
                    <x-metric label="Out-Patients" :value="$stats['outpatients']" tone="green" />
                    <x-metric label="Waiting List" :value="$admissions->whereNull('date_admitted')->count()" tone="orange" />
                    <x-metric label="Upcoming" :value="$appointments->count()" tone="violet" />
                </div>
                <div class="toolbar search-toolbar">
                    <input data-card-search="appointment-list" data-results-target="appointment-search-results" data-empty-target="appointment-empty-state" data-result-label-singular="appointment" data-result-label-plural="appointments" placeholder="Search appointment by patient, consultant, room, date, or recommendation">
                    <button class="primary" type="button" data-search-button="appointment-list">Search</button>
                    <small id="appointment-search-results">{{ $appointments->count() }} appointments shown</small>
                </div>
                <div class="list-card appointment-list">
                    @foreach ($appointments as $appointment)
                        <article data-searchable="{{ strtolower(collect([$appointment->patient_id ?? null, $appointment->patient_first_name ?? null, $appointment->patient_last_name ?? null, $appointment->consultant_first_name ?? null, $appointment->consultant_last_name ?? null, $appointment->appointment_date ?? null, substr($appointment->appointment_time ?? '', 0, 5), $appointment->room ?? null, $appointment->recommendation ?? null])->filter()->implode(' ')) }}"><time>{{ substr($appointment->appointment_time ?? '', 0, 5) }}</time><strong>{{ $appointment->patient_first_name ?? '' }} {{ $appointment->patient_last_name ?? '' }} <small>{{ str_starts_with((string) ($appointment->patient_id ?? ''), 'P') ? $appointment->patient_id : 'P'.str_pad($appointment->patient_id ?? '', 5, '0', STR_PAD_LEFT) }}</small></strong><span>Dr. {{ $appointment->consultant_first_name ?? '' }} {{ $appointment->consultant_last_name ?? '' }}</span><em>{{ $appointment->room ?? 'N/A' }}</em><b>{{ $appointment->recommendation ?? 'Scheduled' }}</b>@if ($canAppointments && ($appointment->appointment_id ?? null))<span class="mini-actions"><button class="icon" type="button" data-modal="appointment-edit-{{ $appointment->appointment_id }}">Edit</button><form class="inline-form" method="POST" action="{{ route('appointments.destroy', $appointment->appointment_id) }}" data-confirm="Delete this appointment?">@csrf @method('DELETE')<button class="icon danger-link" type="submit">Delete</button></form></span>@endif</article>
                    @endforeach
                    <div class="empty-state" id="appointment-empty-state" hidden>No appointments match your search.</div>
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
