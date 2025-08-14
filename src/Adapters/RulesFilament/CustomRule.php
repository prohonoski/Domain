<?php

namespace Proho\Domain\Adapters\RulesFilament;

use Filament\Forms\Components\Field;

class CustomRule
{
    public static function make(Field $field, $rule): Field
    {
        $field->rules([$rule]);
        return $field;
    }
}
