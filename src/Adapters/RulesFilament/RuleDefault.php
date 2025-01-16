<?php

namespace App\Domain\Base\Adapters\RulesFilament;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class RuleDefault
{
    public static function make(Field $field, string $rule): Field
    {
        Log::alert(
            "Regra de validação não encontrada: " .
                $rule .
                " para o campo: " .
                $field->getName()
        );

        try {
            $field->$rule();
        } catch (\Exception $e) {
            //  dd($e->getMessage());
            Log::error(
                "Erro aplicando regra generica " .
                    $rule .
                    " para o campo: " .
                    $field->getName()
            );
        }

        return $field;
    }
}
