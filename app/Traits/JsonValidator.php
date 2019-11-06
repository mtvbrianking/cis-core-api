<?php

namespace App\Traits;

use JsonSchema\Constraints\Constraint;
use App\Exceptions\InvalidJsonException;
use JsonSchema\Constraints\BaseConstraint;

trait JsonValidator
{
    /**
     * Validate JSON against a schema.
     *
     * @param \JsonSchema\Constraints\BaseConstraint $baseConstraint
     * @param string                                 $schemaPath
     * @param string                                 $json
     *
     * @throws \App\Exceptions\InvalidJsonException
     *
     * @return void
     */
    public static function validateJson(BaseConstraint $baseConstraint, string $schemaPath, string $json):void
    {
        $schema = (object) [
            '$ref' => "file:///{$schemaPath}",
        ];

        $value = json_decode($json, false);

        $baseConstraint->validate($value, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        if (! $baseConstraint->isValid()) {
            throw new InvalidJsonException($baseConstraint->getErrors());
        }
    }
}
