<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\BadgeColumn;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class BadgeColumnAdapter implements BadgeColumnInterface
{
    public function __construct(private $field = null) {}

    public static function make(FieldInterface $field)
    {
        $column = BadgeColumn::make($field->getName())->label(
            $field->getLabel(),
        );
        foreach ($field->getColumnAttr() as $key => $value) {
            if (isset($value->getArguments()["enumType"])) {
                $column->formatStateUsing(static function ($state) use (
                    $value,
                ): ?string {
                    if ($state === null) {
                        return $default ?? null;
                    }

                    $enumClass = $value->getArguments()["enumType"];

                    // Safely try to create an enum instance from the state (value)
                    $enumInstance = $enumClass::tryFrom($state);

                    // If the instance was created and it has a getLabel method, use it.
                    if (
                        $enumInstance &&
                        method_exists($enumInstance, "getLabel")
                    ) {
                        return $enumInstance->getLabel();
                    }

                    // Otherwise, fall back to the original logic (show the enum case name).
                    return $enumClass::toArray()[$state] ??
                        ($default ?? $state);
                });
                
            }
        }
        return $column;
    }

    public function generate()
    {
        return static::make($this->field);
    }
}
