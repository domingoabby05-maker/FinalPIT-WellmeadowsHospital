<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';
    protected $primaryKey = 'requisition_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id', 'requisition_id');
    }
}

