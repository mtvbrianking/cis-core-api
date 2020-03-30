<?php

namespace App\Models\Pharmacy;

use App\Models\Facility;
use App\Models\User;
use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasHashedKey, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_stores';

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alias', 'name', 'email', 'password',
    ];

    // Mutators

    /**
     * Set the store name - Title Case.
     *
     * @param string $value
     *
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::title($value);
    }

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
     * Users assigned this store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'pharm_store_user', 'store_id', 'user_id');
    }

    /**
     * Batches belonging to this store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function batches()
    {
        return $this->hasMany(Batch::class, 'store_id', 'id');
    }

    // Scopes

    /**
     * Scope - only those belonging to my facility.
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
