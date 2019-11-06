<?php

namespace App\Traits;

use App\Exceptions\InvalidJsonException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

trait JsonValidation
{
    /**
     * Validate JSON against a schema.
     *
     * @param \JsonSchema\Validator $validator
     * @param string                $schemaPath
     * @param string                $json
     *
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return void
     */
    public static function validateJson(Validator $validator, string $schemaPath, string $json):void
    {
        $value = json_decode($json, false);

        $schema = (object) [
            '$ref' => "file:///{$schemaPath}",
        ];

        $validator->validate($value, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        if (! $validator->isValid()) {
            throw new InvalidJsonException($validator->getErrors());
        }
    }
}
