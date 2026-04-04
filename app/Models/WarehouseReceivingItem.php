<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseReceivingItem extends Model
{
    protected $fillable = [
        'receiving_id', 'material_id', 'expected_qty', 'received_qty',
        'rejected_qty', 'status', 'reject_reason'
    ];

    public function receiving() { return $this->belongsTo(WarehouseReceiving::class); }
    public function material() { return $this->belongsTo(Material::class); }
}