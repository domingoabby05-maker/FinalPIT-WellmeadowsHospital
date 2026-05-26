            <section class="screen" id="medications-screen">
                <header class="page-head"><div><h1>Medication Tracking</h1><p>Record and monitor patient medications</p></div>@if ($canMedications)<button class="primary" data-modal="medication-modal">+ Prescribe Medication</button>@endif</header>
                <div class="metric-grid">
                    <x-metric label="Active Prescriptions" :value="$stats['active_prescriptions']" tone="blue" />
                    <x-metric label="Medications Today" :value="$medications->count()" tone="green" />
                    <x-metric label="Drug Types" :value="$stats['drug_types']" tone="violet" />
                    <x-metric label="Expiring Soon" :value="$medications->where('end_date', '<=', now()->addDays(7)->toDateString())->count()" tone="orange" />
                </div>
                <div class="toolbar search-toolbar">
                    <input data-search="medications-table" placeholder="Search medication by patient, drug, ward, method, or date">
                    <button class="primary" type="button" data-table-search-button="medications-table">Search</button>
                </div>
                <div class="table-card">
                    <table id="medications-table"><thead><tr><th>Patient</th><th>Ward/Bed</th><th>Medication</th><th>Dosage</th><th>Method</th><th>Units/Day</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                    @foreach ($medications as $medication)
                        <tr><td>{{ $medication->first_name }} {{ $medication->last_name }}<small>{{ str_starts_with((string) $medication->patient_id, 'P') ? $medication->patient_id : 'P'.str_pad($medication->patient_id, 5, '0', STR_PAD_LEFT) }}</small></td><td>{{ $medication->ward_name ?? 'Out-Patient Clinic' }}<small>Bed {{ $medication->bed_number ?? 'N/A' }}</small></td><td><strong>{{ $medication->drug_name }}</strong><small>{{ $medication->description }}</small></td><td>{{ $medication->dosage }}</td><td><span class="pill ok">{{ $medication->method_of_admin }}</span></td><td>{{ $medication->units_per_day }}</td><td>{{ $medication->start_date }}<small>{{ $medication->end_date ?? 'Ongoing' }}</small></td><td><span class="pill ok">Active</span></td><td>@if ($canMedications)<button class="icon" type="button" data-modal="medication-edit-{{ $medication->medication_id }}">Edit</button><form class="inline-form" method="POST" action="{{ route('medications.destroy', $medication->medication_id) }}" data-confirm="Delete this medication record?">@csrf @method('DELETE')<button class="icon danger-link" type="submit">Delete</button></form>@else<span class="pill">View only</span>@endif</td></tr>
                    @endforeach
                    </tbody></table>
                </div>
            </section>
