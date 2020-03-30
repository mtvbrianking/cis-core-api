<?php

namespace App\Models\Pharmacy;

use App\Traits\HasHashedKey;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasHashedKey;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pharm_inventory';
}
