<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $table = 'requisition_items';
    protected $primaryKey = 'requisition_item_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'requisition_id', 'requisition_id');
    }
}

