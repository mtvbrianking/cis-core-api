<?php

namespace App\Traits;

use App\Exceptions\InvalidJsonException;
use Illuminate\Http\Request;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

trait JsonValidation
{
    /**
     * Validate JSON against a schema.
     *
     * @param \JsonSchema\Validator    $validator
     * @param string                   $schemaPath
     * @param \Illuminate\Http\Request $request
     *
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return void
     */
    public static function validateJson(Validator $validator, string $schemaPath, Request $request):void
    {
        $options = $request->query() ? JSON_NUMERIC_CHECK : JSON_FORCE_OBJECT;

        $query = json_encode($request->query(), $options);

        $value = json_decode($query, false);

        $schema = (object) [
            '$ref' => "file:///{$schemaPath}",
        ];

        $validator->validate($value, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        if (! $validator->isValid()) {
            throw new InvalidJsonException($validator->getErrors());
        }
    }
}
