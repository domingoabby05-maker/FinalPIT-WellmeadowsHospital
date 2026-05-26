<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class PatientReferral extends Model
{
    protected $table = 'patient_referrals';
    protected $primaryKey = 'referral_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(LocalDoctor::class, 'doctor_id', 'doctor_id');
    }
}

