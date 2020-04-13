<?php

namespace App\Models;

use Bmatovu\Uuid\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read Facility $facility
 *
 * @method Builder|Station onlyRelated(User $user)
 */
class Station extends Model
{
    use HasUuidKey, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stations';

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    // Relationships

    /**
     * Facility for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    /**
     * Users assigned this station.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'station_user', 'station_id', 'user_id', 'id');
    }

    /**
     * Visits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visits()
    {
        return $this->belongsToMany(Visit::class, 'station_visit', 'station_id', 'visit_id', 'id')
            ->withPivot([
                'user_id',
                'instructions',
                'accepted_at',
                'concluded_at',
                'canceled_at',
            ])
            ->withTimestamps();
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
        return $query->where('patients.facility_id', $user->facility_id);
    }
}
