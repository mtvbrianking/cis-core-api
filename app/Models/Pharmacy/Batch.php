<?php

namespace App\Models\Pharmacy;

use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_batches';

    // Relationships

    /**
     * Product to which this batch belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
