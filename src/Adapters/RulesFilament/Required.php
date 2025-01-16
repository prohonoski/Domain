<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class Required
{
    public static function make(Field $field, string $rule): Field
    {
        //if ($field instanceof TextInput) {
        $field->required();
        //}
        return $field;
    }
}
