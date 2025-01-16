<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class Gte
{
    public static function make(Field $field, string $rule): Field
    {
        try {
            $size = intval($rule);
        } catch (\Exception $e) {
            $size = 0;
        }

        //        if ($field instanceof TextInput) {

        $field->gte(3);
        //}
        return $field;
    }
}
