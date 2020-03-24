<?php

namespace App\Models\Pharmacy;

use App\Models\Facility;
use App\Models\User;
use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasHashedKey, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_product';

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
     * Batches belonging to this product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
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
