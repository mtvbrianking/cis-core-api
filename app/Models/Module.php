<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules model.
 */
class Module extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modules';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'name';

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // Mutators

    /**
     * Set the module name - as slug.
     *
     * @param string $value
     *
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::slug(Str::plural($value));
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
     * Permissions belonging to this module.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_name', 'name');
    }

    /**
     * Facilities assigned this facility.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_module', 'module_name', 'facility_id');
    }
}
