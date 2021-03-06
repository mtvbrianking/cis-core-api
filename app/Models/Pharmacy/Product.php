<?php

namespace App\Models\Pharmacy;

use App\Models\Facility;
use App\Models\User;
use Bmatovu\Uuid\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuidKey, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_products';

    // Relationships

    /**
     * Facility for this store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    /**
     * Stores having this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'pharm_store_product', 'product_id', 'store_id', 'id')
            ->withPivot([
                'quantity',
                'unit_price',
            ]);
    }

    /**
     * Purchases having this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchases()
    {
        return $this->belongsToMany(Purchase::class, 'pharm_purchase_product', 'product_id', 'purchase_id', 'id')
            ->withPivot([
                'supplier_id',
                'quantity',
                'unit_price',
                'mfr_batch_no',
                'mfd_at',
                'expires_at',
            ]);
    }

    /**
     * Purchases having this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'pharm_sale_product', 'product_id', 'sale_id', 'id')
            ->withPivot([
                'quantity',
                'price',
            ]);
    }

    // Scopes

    /**
     * Scope - only those belonging to a users' facility.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User                      $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnlyRelated(Builder $query, User $user)
    {
        return $query->where("{$this->table}.facility_id", $user->facility_id);
    }
}
