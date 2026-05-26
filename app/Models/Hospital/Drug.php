<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $table = 'drugs';
    protected $primaryKey = 'drug_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function medications()
    {
        return $this->hasMany(PatientMedication::class, 'drug_id', 'drug_id');
    }
}

