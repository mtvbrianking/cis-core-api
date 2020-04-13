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
}
