<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
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
