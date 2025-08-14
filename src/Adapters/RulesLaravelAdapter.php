<?php

namespace Proho\Domain\Adapters;

use Proho\Domain\Field;

class RulesLaravelAdapter
{
    public static function make(Field $field, string $rule): Field
    {
        return $field;
    }
}
