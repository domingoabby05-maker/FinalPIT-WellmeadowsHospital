<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class WardStaff extends Model
{
    protected $table = 'ward_staff';
    protected $primaryKey = 'ward_staff_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'ward_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}

