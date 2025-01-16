<?php

namespace Proho\Domain\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class Gt
{
    public static function make(Field $field, string $rule): Field
    {
        try {
            $size = intval($rule);
        } catch (\Exception $e) {
            $size = 0;
        }

        $field->gt(3);
        return $field;
    }
}
