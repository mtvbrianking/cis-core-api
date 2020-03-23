<?php

namespace App\Models\Pharmacy;

use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_product';

    // Relationships

    /**
     * Batches belonging to this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
