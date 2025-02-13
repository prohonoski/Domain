<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class TextBadgeColumnAdapter implements BadgeColumnInterface
{
    private TextColumn $column;

    public function __construct(private $field) {}

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
            if (isset($value->getArguments()["enumType"])) {
                $column->formatStateUsing(
                    static fn($state): ?string => $value
                        ->getArguments()
                        ["enumType"]::toArray()[$state] ??
                        ($default ?? $state)
                );
                //   dd($value->getArguments()["enumType"]);
                //
                //

                //$column->enum($value->getArguments()["enumType"]::toArray());
                //->colors(
                // [
                //     "primary" => static fn(
                //         $state
                //     ): bool => $state == 1 || $state == 4,
                // ]

                // ->colors(static function ($state) use ($value) {
                //     dd("aaa");
                //     dd(
                //         $value
                //             ->getArguments()
                //             ["enumType"]::getColors(["state" => $state])
                //     );

                //     return $value
                //         ->getArguments()
                //         ["enumType"]::getColors($state);
                // }
                //);
                //);
                // ->colors([
                //     "primary" => static fn($state): bool => $state ==
                //         1 || $state == 4,
                //     "warning" => static fn($state): bool => $state == 2,
                //     "success" => static fn($state): bool => $state == 3,
                //     "secondary" => static fn($state): bool => in_array(
                //         $state,
                //         [5, 6, 7]
                //     ),
                // ]);
            }
        }
        return $column;
    }

    public function create(): TextColumn
    {
        return $this->column;
    }
}
