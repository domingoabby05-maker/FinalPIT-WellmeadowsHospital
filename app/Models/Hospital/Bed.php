<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table = 'beds';
    protected $primaryKey = 'bed_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'ward_id');
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'bed_id', 'bed_id');
    }
}

