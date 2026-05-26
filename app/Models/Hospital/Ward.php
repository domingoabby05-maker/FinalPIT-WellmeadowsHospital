<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'wards';
    protected $primaryKey = 'ward_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function beds()
    {
        return $this->hasMany(Bed::class, 'ward_id', 'ward_id');
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'ward_id', 'ward_id');
    }

    public function staffAssignments()
    {
        return $this->hasMany(WardStaff::class, 'ward_id', 'ward_id');
    }
}

