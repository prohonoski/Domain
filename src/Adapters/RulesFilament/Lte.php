<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;

class Lte
{
    public static function make(Field $field, string $rule): Field
    {
        try {
            $size = intval(explode(":", $rule)[1]);
        } catch (\Exception $e) {
            $size = 0;
        }

        //if ($field instanceof TextInput) {
        $field->lte($size);
        //}
        return $field;
    }
}
