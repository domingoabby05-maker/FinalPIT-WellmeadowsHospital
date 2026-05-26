<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'patient_id', 'patient_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function nextOfKin()
    {
        return $this->hasMany(NextOfKin::class, 'patient_id', 'patient_id');
    }

    public function medications()
    {
        return $this->hasMany(PatientMedication::class, 'patient_id', 'patient_id');
    }
}

