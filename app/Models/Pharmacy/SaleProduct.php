<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SaleProduct extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    // Relationships

    /**
     * Sale this entry belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    /**
     * Product in this entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
