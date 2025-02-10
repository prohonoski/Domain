<?php

namespace Proho\Domain\Adapters\RulesFilament;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class AfterOrEqual
{
    public static function make(Field $field, string $rule): Field
    {
        if ($field instanceof DatePicker || $field instanceof DateTimePicker) {
            $field->afterOrEqual($rule);
        }

        return $field;
    }
}
