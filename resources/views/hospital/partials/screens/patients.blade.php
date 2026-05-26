            <section class="screen" id="patients-screen">
                <header class="page-head"><div><h1>Patient Management</h1><p>Manage patient records and information</p></div>@if ($canPatients)<button class="primary" data-modal="patient-modal">+ Register Patient</button>@endif</header>
                <div class="toolbar"><input data-search="patients-table" placeholder="Search by name or patient ID"><select data-table-filter="patients-table" data-filter-column="status"><option value="">All Status</option><option value="in-patient">In-Patient</option><option value="out-patient">Out-Patient</option><option value="waiting-list">Waiting List</option></select></div>
                <div class="table-card">
                    <table id="patients-table">
                        <thead><tr><th>Patient ID</th><th>Name</th><th>Age/Sex</th><th>Contact</th><th>Next of Kin</th><th>Local Doctor</th><th>Appointment</th><th>Status</th><th>Ward/Bed</th><th>Actions</th></tr></thead>
                        <tbody>
                        @foreach ($patients->unique('patient_id') as $patient)
                            @php
                                $age = $patient->date_of_birth ? \Illuminate\Support\Carbon::parse($patient->date_of_birth)->age : 'N/A';
                                $status = $patient->date_admitted
                                    ? 'In-Patient'
                                    : match ($patient->appointment_recommendation ?? null) {
                                        'Out-Patient Clinic' => 'Out-Patient',
                                        'In-Patient Admission', 'Waiting List' => 'Waiting List',
                                        default => ($patient->appointment_id ?? null) ? 'Scheduled' : 'Waiting List',
                                    };
                                $statusClass = in_array($status, ['In-Patient', 'Out-Patient'], true) ? 'ok' : 'warn';
                            @endphp
                            <tr data-status="{{ strtolower(str_replace(' ', '-', $status)) }}">
                                <td>{{ str_starts_with((string) $patient->patient_id, 'P') ? $patient->patient_id : 'P'.str_pad($patient->patient_id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td><span class="avatar">{{ substr($patient->first_name, 0, 1) }}</span>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                                <td>{{ $age }} / {{ $patient->sex }}</td>
                                <td>{{ $patient->phone }}<small>{{ $patient->address }}</small></td>
                                <td>{{ $patient->kin_name ?? 'N/A' }}<small>{{ $patient->kin_relationship ?? 'No relationship listed' }}{{ ($patient->kin_phone ?? null) ? ' - '.$patient->kin_phone : '' }}</small></td>
                                <td>{{ $patient->doctor_name ?? 'N/A' }}<small>{{ $patient->clinic_number ?? 'No clinic number' }}{{ ($patient->referral_date ?? null) ? ' - '.$patient->referral_date : '' }}</small></td>
                                <td>{{ $patient->appointment_date ?? 'No appointment' }}<small>{{ ($patient->appointment_time ?? null) ? substr($patient->appointment_time, 0, 5) : 'N/A' }}{{ ($patient->appointment_room ?? null) ? ' - '.$patient->appointment_room : '' }}{{ ($patient->appointment_consultant ?? null) ? ' - '.$patient->appointment_consultant : '' }}</small></td>
                                <td><span class="pill {{ $statusClass }}">{{ $status }}</span><small>{{ $patient->appointment_recommendation ?? 'No appointment recommendation' }}</small></td>
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
