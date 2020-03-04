<?php

namespace App\Models;

use Bmatovu\Uuid\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \App\Models\Module $modules
 * @property-read \App\Models\User $users
 * @property-read \App\Models\Role $roles
 */
class Facility extends Model
{
    use HasUuidKey, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facilities';

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
        'name',
        'description',
        'address',
        'email',
        'website',
        'phone',
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
        // ...
    ];

    // Relationships

    /**
     * Users belonging to this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'facility_id', 'id');
    }

    /**
     * User roles belonging to this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {
        return $this->hasMany(Role::class, 'facility_id', 'id');
    }

    /**
     * Modules assigned to this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'facility_module', 'facility_id', 'module_name');
    }

    /**
     * Pharmacy stores belonging to this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pharm_stores()
    {
        return $this->hasMany(Pharmacy\Store::class, 'facility_id', 'id');
    }
}
