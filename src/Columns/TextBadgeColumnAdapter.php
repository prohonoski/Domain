<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Contracts\HasColor;
use Proho\Domain\Contracts\HasLabel;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class TextBadgeColumnAdapter implements BadgeColumnInterface
{
    private TextColumn $column;

    //public function __construct(private $field) {}

    public function generate()
    {
        return static::make($this->field);
    }

    public static function make(FieldInterface $field): TextColumn
    {
        $column = TextColumn::make($field->getName())
            ->badge()
            ->label($field->getLabel());

        foreach ($field->getColumnAttr() as $key => $value) {
            $enumClass = $value->getArguments()["enumType"];
            // Safely try to create an enum instance from the state (value)

            if (is_subclass_of($enumClass, HasLabel::class)) {
                $column->formatStateUsing(static function ($state) use (
                    $value,
                    $enumClass,
                ): ?string {
                    $enumInstance = $enumClass::tryFrom($state);
                    return $enumInstance->getLabel();
                });
            }

            if (is_subclass_of($enumClass, HasColor::class)) {
                $column->color(static function ($state) use (
                    $value,
                    $enumClass,
                ): ?string {
                    $enumInstance = $enumClass::tryFrom($state);
                    return $enumInstance->getColor();
                });
            }
        }
        return $column;
    }

    public function create(): TextColumn
    {
        return $this->column;
    }
}
