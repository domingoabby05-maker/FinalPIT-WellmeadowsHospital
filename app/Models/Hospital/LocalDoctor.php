<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class LocalDoctor extends Model
{
    protected $table = 'local_doctors';
    protected $primaryKey = 'doctor_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function referrals()
    {
        return $this->hasMany(PatientReferral::class, 'doctor_id', 'doctor_id');
    }
}

