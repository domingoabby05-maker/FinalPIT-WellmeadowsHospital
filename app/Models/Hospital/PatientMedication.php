<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class PatientMedication extends Model
{
    protected $table = 'patient_medication';
    protected $primaryKey = 'medication_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id', 'drug_id');
    }
}

