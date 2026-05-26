<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'consultant_id', 'staff_id');
    }

    public function wardAssignments()
    {
        return $this->hasMany(WardStaff::class, 'staff_id', 'staff_id');
    }

    public function contracts()
    {
        return $this->hasMany(StaffContract::class, 'staff_id', 'staff_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'staff_id', 'staff_id');
    }

    public function workExperience()
    {
        return $this->hasMany(WorkExperience::class, 'staff_id', 'staff_id');
    }
}

