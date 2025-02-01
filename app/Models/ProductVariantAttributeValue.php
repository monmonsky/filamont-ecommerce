<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariantAttributeValue extends Pivot
{
    protected $table = 'product_variant_attribute_value';

    protected $fillable = [
        'product_variant_id',
        'attribute_value_id',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    
}
