<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function consultant()
    {
        return $this->belongsTo(Staff::class, 'consultant_id', 'staff_id');
    }

    public function outpatient()
    {
        return $this->hasOne(Outpatient::class, 'appointment_id', 'appointment_id');
    }
}

