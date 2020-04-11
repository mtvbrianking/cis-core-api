<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PurchaseProduct extends Pivot
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_purchase_product';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'unit_price' => 'double',
    ];

    // Relationships

    /**
     * Purchase.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
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
