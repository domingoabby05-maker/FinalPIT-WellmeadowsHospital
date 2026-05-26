<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function supplies()
    {
        return $this->hasMany(Supply::class, 'supplier_id', 'supplier_id');
    }
}

