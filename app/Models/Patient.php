<?php

namespace App\Models;

use Bmatovu\Uuid\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read Facility $facility
 *
 * @method Builder|Patient onlyRelated(User $user)
 */
class Patient extends Model
{
    use HasUuidKey, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patients';

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
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'nin',
        'weight',
        'height',
        'blood_type',
        'existing_conditions',
        'allergies',
        'notes',
        'next_of_kin',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'weight' => 'double',
        'height' => 'double',
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
     * Visits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visits()
    {
        return $this->hasMany(Visit::class, 'user_id', 'id');
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
