<?php

namespace Proho\Domain\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class MaxDigits
{
    public static function make(Field $field, string $rule): Field
    {
        try {
            $size = intval($rule);
        } catch (\Exception $e) {
            $size = 0;
        }

        if ($field instanceof TextInput || $field instanceof Textarea) {
            $field->maxLength($size);
        }
        return $field;
    }
}
