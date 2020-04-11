<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SaleProduct extends Pivot
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_sale_product';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal',
        'price' => 'decimal',
    ];

    // Relationships

    /**
     * Sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }

    /**
     * Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
