<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseSection extends Model
{
    protected $fillable = ['warehouse_id', 'name', 'grid_row', 'grid_col', 'capacity'];

    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function shelves() { return $this->hasMany(WarehouseShelf::class, 'section_id'); }
}