<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_sales';

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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tax_rate' => 'double',
        'total' => 'double',
    ];

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
     * User that made this sale.
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
        return $this->belongsToMany(Product::class, 'pharm_sale_product', 'sale_id', 'product_id', 'id')
            ->withPivot([
                'quantity',
                'price',
            ]);
    }
}
