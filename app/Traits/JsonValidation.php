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
     * @see https://json-schema.org/specification.html JSON Schema Specification
     * @see https://jsonschema.net JSON Schema Generator
     * @see https://www.jsonschemavalidator.net JSON Schema Validator
     * @see https://json-schema.org/understanding-json-schema/structuring.html Tutorial
     *
     * @param \JsonSchema\Validator $validator
     * @param string                $schemaPath
     * @param array                 $data
     *
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return void
     */
    public static function validateJson(Validator $validator, string $schemaPath, array $data): void
    {
        $options = empty($data) ? JSON_FORCE_OBJECT : JSON_NUMERIC_CHECK;

        $query = json_encode($data, $options);

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
