<?php

namespace App\Domain\Base\Adapters;

use App\Domain\Base\Field;

class RulesLaravelAdapter
{
    public static function make(Field $field, string $rule): Field
    {
        return $field;
    }
}
