<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class AfterOrEqual
{
    public static function make(Field $field, string $rule): Field
    {
        if ($field instanceof DatePicker) {
            $field->afterOrEqual($rule);
        }
        return $field;
    }
}
