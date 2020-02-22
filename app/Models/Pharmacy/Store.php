<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_stores';

    // Relationships

    /**
     * Users assigned this store.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'pharm_user_store', 'store_id', 'user_id');
    }
}
