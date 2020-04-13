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
}
