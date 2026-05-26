<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class StaffContract extends Model
{
    protected $table = 'staff_contracts';
    protected $primaryKey = 'contract_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}

