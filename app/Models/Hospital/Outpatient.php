<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Outpatient extends Model
{
    protected $table = 'outpatients';
    protected $primaryKey = 'outpatient_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
}

