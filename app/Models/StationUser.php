<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class StationUser extends Pivot
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'station_user';
}
