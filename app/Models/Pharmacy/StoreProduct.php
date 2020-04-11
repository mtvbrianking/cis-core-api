<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StoreProduct extends Pivot
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_store_product';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'double',
    ];

    // Relationships

    /**
     * Store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
