<?php

namespace Proho\Domain\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class RequiredWith
{
    public static function make(Field $field, string $value): Field
    {
        $field->requiredWith($value);

        return $field;
    }
}
