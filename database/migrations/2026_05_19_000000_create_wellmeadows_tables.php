<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->id('patient_id');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('address');
                $table->string('phone', 30)->nullable();
                $table->date('date_of_birth');
                $table->char('sex', 1);
                $table->string('marital_status', 30)->nullable();
                $table->date('date_registered');
            });
        }

        if (! Schema::hasTable('next_of_kin')) {
            Schema::create('next_of_kin', function (Blueprint $table) {
                $table->id('kin_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->string('full_name');
                $table->string('relationship', 80);
                $table->string('address');
                $table->string('phone', 30)->nullable();
            });
        }

        if (! Schema::hasTable('local_doctors')) {
            Schema::create('local_doctors', function (Blueprint $table) {
                $table->id('doctor_id');
                $table->string('full_name');
                $table->string('clinic_number')->unique();
                $table->string('address');
                $table->string('phone', 30)->nullable();
            });
        }

        if (! Schema::hasTable('patient_referrals')) {
            Schema::create('patient_referrals', function (Blueprint $table) {
                $table->id('referral_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('local_doctors', 'doctor_id');
                $table->date('referral_date');
            });
        }

        if (! Schema::hasTable('staff')) {
            Schema::create('staff', function (Blueprint $table) {
                $table->id('staff_id');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('address');
                $table->string('phone', 30)->nullable();
                $table->date('date_of_birth');
                $table->char('sex', 1);
                $table->string('nin', 30)->unique();
                $table->string('staff_position', 100);
                $table->decimal('salary', 10, 2);
                $table->string('salary_scale', 20)->nullable();
            });
        }

        if (! Schema::hasTable('staff_contracts')) {
            Schema::create('staff_contracts', function (Blueprint $table) {
                $table->id('contract_id');
                $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
                $table->decimal('hours_per_week', 5, 2);
                $table->string('contract_type', 30);
                $table->string('payment_type', 30);
            });
        }

        if (! Schema::hasTable('work_experience')) {
            Schema::create('work_experience', function (Blueprint $table) {
                $table->id('experience_id');
                $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
                $table->string('staff_position', 100);
                $table->string('organization');
                $table->date('start_date');
                $table->date('end_date')->nullable();
            });
        }

        if (! Schema::hasTable('qualifications')) {
            Schema::create('qualifications', function (Blueprint $table) {
                $table->id('qualification_id');
                $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
                $table->string('qualification_type');
                $table->string('institution');
                $table->date('date_obtained');
            });
        }

        if (! Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->id('ward_id');
                $table->string('ward_name', 100);
                $table->string('location', 100);
                $table->unsignedInteger('total_beds');
                $table->string('tel_extension', 20)->nullable();
            });
        }

        if (! Schema::hasTable('ward_staff')) {
            Schema::create('ward_staff', function (Blueprint $table) {
                $table->id('ward_staff_id');
                $table->foreignId('ward_id')->constrained('wards', 'ward_id')->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained('staff', 'staff_id')->cascadeOnDelete();
                $table->string('role', 100);
                $table->string('shift', 30);
            });
        }

        if (! Schema::hasTable('beds')) {
            Schema::create('beds', function (Blueprint $table) {
                $table->id('bed_id');
                $table->foreignId('ward_id')->constrained('wards', 'ward_id')->cascadeOnDelete();
                $table->unsignedInteger('bed_number');
                $table->string('status', 30)->default('Available');
                $table->unique(['ward_id', 'bed_number']);
            });
        }

        if (! Schema::hasTable('admissions')) {
            Schema::create('admissions', function (Blueprint $table) {
                $table->id('admission_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->foreignId('ward_id')->constrained('wards', 'ward_id');
                $table->foreignId('bed_id')->nullable()->constrained('beds', 'bed_id');
                $table->date('date_placed_on_waiting_list')->nullable();
                $table->unsignedInteger('expected_stay_days')->nullable();
                $table->date('date_admitted')->nullable();
                $table->date('expected_leave_date')->nullable();
                $table->date('actual_leave_date')->nullable();
            });
        }

        if (! Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id('appointment_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->foreignId('consultant_id')->constrained('staff', 'staff_id');
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->string('room', 50);
            });
        }

        if (! Schema::hasTable('outpatients')) {
            Schema::create('outpatients', function (Blueprint $table) {
                $table->id('outpatient_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('drugs')) {
            Schema::create('drugs', function (Blueprint $table) {
                $table->id('drug_id');
                $table->string('name', 100);
                $table->string('description')->nullable();
                $table->string('dosage', 80)->nullable();
                $table->string('method_of_admin', 80)->nullable();
                $table->unsignedInteger('quantity_in_stock')->default(0);
                $table->unsignedInteger('reorder_level')->default(0);
                $table->decimal('cost_per_unit', 10, 2)->default(0);
            });
        }

        if (! Schema::hasTable('patient_medication')) {
            Schema::create('patient_medication', function (Blueprint $table) {
                $table->id('medication_id');
                $table->foreignId('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
                $table->foreignId('drug_id')->constrained('drugs', 'drug_id');
                $table->unsignedInteger('units_per_day');
                $table->date('start_date');
                $table->date('end_date')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'patient_medication',
            'drugs',
            'outpatients',
            'appointments',
            'admissions',
            'beds',
            'ward_staff',
            'wards',
            'qualifications',
            'work_experience',
            'staff_contracts',
            'staff',
            'patient_referrals',
            'local_doctors',
            'next_of_kin',
            'patients',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
