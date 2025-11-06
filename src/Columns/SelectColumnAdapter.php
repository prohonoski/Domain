<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Interfaces\TextColumnInterface;

class SelectColumnAdapter implements TextColumnInterface
{
    public static function make(FieldInterface $field)
    {
        // $enumClass =
        //     $field?->getColumnAttr()[0]?->getArguments()["enumType"] ?? null;
        // $enumInstance = class_exists($enumClass ?? "")
        //     ? new $enumClass()
        //     : null;
        //
        //

        $enumClass = ($field?->getColumnAttr()[0] ?? null)?->getArguments()["enumType"] ?? null;

        //dd($enumClass);

        if ($enumClass ) {
            $component = BadgeColumn::make($field->getName())
                ->label($field->getLabel())
                ->sortable($field->isSortable())
                ->wrap($field->isWrap())
                ->formatStateUsing(function ($state) use ($field) {

                    $enumClass = ($field?->getColumnAttr()[0] ?? null)?->getArguments()["enumType"] ?? null;

                    if (
                        $enumClass &&
                        class_exists($enumClass) &&
                        enum_exists($enumClass)
                    ) {
                        try {
                            $enum = $enumClass::from($state); // cria a instância do enum com o valor atual
                            // supondo que seu enum tem um método getLabel() ou label()
                            return method_exists($enum, "getLabel")
                                ? $enum->getLabel()
                                : $enum->label ?? $state;
                        } catch (\ValueError) {
                            // valor inválido para o enum, retorna o valor cru
                            return $state;
                        }
                    }

                    return $state;
                });
        }
        else {

            // Obtém relação com fallback para array vazio
            $relation = method_exists($field, 'getRelation')
                ? ($field->getRelation() ?? [])
                : [];

            // Verifica e extrai class
            $class = $relation['class'] ?? null;

            if (!$class || !is_string($class)) {
                // Sem relação válida, usar nome do campo
                $columnName = $field->getName();
            } else {
                // Monta o nome da coluna com relação
                $className = str_replace('Entity', '', class_basename($class));

                $label = $relation['label'] ?? null;
                $firstLabel = (is_array($label) && isset($label[0])) ? $label[0] : '';

                $columnName = $firstLabel ? "{$className}.{$firstLabel}" : $className;
            }

            $component = TextColumn::make($columnName)
                ->label($field->getLabel())
                ->sortable($field->isSortable())
                ->wrap($field->isWrap());
        }


        return $component;
    }
}
