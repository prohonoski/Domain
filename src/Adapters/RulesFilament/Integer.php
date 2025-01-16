<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class Integer
{
    public static function make(Field $field, string $rule): Field
    {
        //if ($field instanceof TextInput) {

        //}
        $field->integer();
        return $field;
    }
}
