<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_catalog';




    // Relationships

    /**
     * Batches belonging to this catalog.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}