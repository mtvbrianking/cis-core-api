<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Roles model.
 */
class Role extends Model
{
    use SoftDeletes, Uuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

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
        'name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pivot',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // ..
    ];

    // Mutators

    /**
     * Set the role name - Title Case.
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
     * User that created this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

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
     * Users having this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Permissions assigned to this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->BelongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id', 'id');
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
    public function scopeOnlyRelated($query, $user)
    {
        return $query->where('facility_id', $user->facility_id);
    }
}
