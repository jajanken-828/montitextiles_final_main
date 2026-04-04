<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseShelf extends Model
{
    protected $fillable = ['section_id', 'shelf_number'];

    public function section() { return $this->belongsTo(WarehouseSection::class); }
    public function stockItems() { return $this->hasMany(WarehouseStockItem::class); }
}