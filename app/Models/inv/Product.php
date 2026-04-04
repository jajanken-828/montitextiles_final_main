<?php

namespace App\Models\inv;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'category',
        'subcategory',
        'status',
        'color_tag',
        'color_hex',
        'color_name',
        'weight',
        'dimensions',
        'batch_size',
        'lead_time',
        'unit_cost',
        'selling_price',
        'stock_on_hand',
        'moq',
        'certification',
        'description',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'selling_price' => 'float',
        'stock_on_hand' => 'integer',
        'batch_size' => 'integer',
        'moq' => 'integer',
    ];

    /**
     * Get the available sizes for this product.
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order');
    }

    /**
     * Get the Bill of Materials (BOM) entries for this product.
     */
    public function bom(): HasMany
    {
        return $this->hasMany(ProductBom::class)->orderBy('sort_order');
    }

    /**
     * Get the technical specifications for this product.
     */
    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    /**
     * Get the product images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Accessor for the gross margin percentage.
     */
    public function getGrossMarginAttribute(): float
    {
        if ($this->selling_price <= 0) {
            return 0;
        }

        return round((($this->selling_price - $this->unit_cost) / $this->selling_price) * 100, 1);
    }

    /**
     * Generate the next product ID (e.g., PRD-001, PRD-002...).
     */
    public static function nextProductId(): string
    {
        $last = static::orderByDesc('id')->value('product_id');
        if (! $last) {
            return 'PRD-001';
        }
        $num = (int) substr($last, 4) + 1;
        return 'PRD-'.str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}