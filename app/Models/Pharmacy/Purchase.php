<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_purchases';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    // Relationships

    /**
     * Store this sale belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * User that made this purchase.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Products in this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'pharm_purchase_product', 'purchase_id', 'product_id', 'id')
            ->withPivot([
                'supplier_id',
                'quantity',
                'unit_price',
                'mfr_batch_no',
                'mfd_at',
                'expires_at',
            ]);
    }
}
