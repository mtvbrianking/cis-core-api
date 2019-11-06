<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Trait Uuids.
 *
 * @link https://medium.com/@steveazz/setting-up-uuids-in-laravel-5-552412db2088
 * @see https://stackoverflow.com/a/54817274/2732184 Trigger create model issue
 */
trait Uuids
{
    public function getIncrementing():bool
    {
        return false;
    }

    public function getKeyType():string
    {
        return 'string';
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot():void
    {
        parent::boot();

        static::creating(function (Model $model): void {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            }
        });
    }
}
