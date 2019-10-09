<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;

/**
 * Trait Uuids.
 *
 * @link https://medium.com/@steveazz/setting-up-uuids-in-laravel-5-552412db2088
 * @see https://stackoverflow.com/a/54817274/2732184 Trigger create model issue
 */
trait Uuids
{
    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }
}
