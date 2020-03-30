<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasHashedKey
{
    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * The "booting" method of the model.
     *
     * @see https://stackoverflow.com/a/28171796 Ref
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $model): void {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = base_convert(microtime(true), 10, 16);
            }
        });
    }
}
