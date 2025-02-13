<?php

namespace Proho\Domain\Adapters;

use Filament\Tables\Columns\BadgeColumn;
use Proho\Domain\Enums\FieldTypesEnum;
use Filament\Tables\Columns\TextColumn;
use OpenSpout\Common\Entity\Comment\TextRun;
use Proho\Domain\Columns\BadgeColumn as ColumnsBadgeColumn;
use Proho\Domain\Columns\TextColumnAdapter;
use Proho\Domain\Columns\TextDateColumnAdapter;
use Proho\Domain\Columns\TextDateTimeColumnAdapter;
use Proho\Domain\Columns\TextDecimalColumnAdapter;
use Proho\Domain\Columns\TextNumericColumnAdapter;
use Proho\Domain\Field;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class ColumnFilamentAdapter
{
    private $columnField;

    public function getColumnField()
    {
        return $this->columnField;
    }

    function __construct(FieldInterface $field)
    {
        $this->columnField = null;

        match ($field->getType()) {
            FieldTypesEnum::String
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::StringLong
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::TextArea
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::Decimal
                => ($this->columnField = TextNumericColumnAdapter::make(
                $field
            )),
            FieldTypesEnum::Integer
                => ($this->columnField = TextNumericColumnAdapter::make(
                $field
            )),
            FieldTypesEnum::DateTime
                => ($this->columnField = TextDateTimeColumnAdapter::make(
                $field
            )),
            FieldTypesEnum::Date
                => ($this->columnField = TextDateColumnAdapter::make($field)),
            FieldTypesEnum::Select
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::Radio => ($this->columnField = app(
                BadgeColumnInterface::class,
                ["field" => $field]
            )->generate()),
            default => dd([
                "Campo sem correspondencia",
                $field->getType(),
                $field->getName(),
            ]),
        };

        //    if ($this->columnField->getName() == "situacao") {
        // foreach ($field->getColumnAttr() as $key => $value) {
        //     if (isset($value->getArguments()["enumType"])) {
        //         //   dd($value->getArguments()["enumType"]);
        //         //
        //         //

        //         $this->columnField->enum(
        //             $value->getArguments()["enumType"]::toArray()
        //         );
        //         //->colors(
        //         // [
        //         //     "primary" => static fn(
        //         //         $state
        //         //     ): bool => $state == 1 || $state == 4,
        //         // ]

        //         // ->colors(static function ($state) use ($value) {
        //         //     dd("aaa");
        //         //     dd(
        //         //         $value
        //         //             ->getArguments()
        //         //             ["enumType"]::getColors(["state" => $state])
        //         //     );

        //         //     return $value
        //         //         ->getArguments()
        //         //         ["enumType"]::getColors($state);
        //         // }
        //         //);
        //         //);
        //         // ->colors([
        //         //     "primary" => static fn($state): bool => $state ==
        //         //         1 || $state == 4,
        //         //     "warning" => static fn($state): bool => $state == 2,
        //         //     "success" => static fn($state): bool => $state == 3,
        //         //     "secondary" => static fn($state): bool => in_array(
        //         //         $state,
        //         //         [5, 6, 7]
        //         //     ),
        //         // ]);
        //     }
        //}
        //}

        // if ($this->columnField) {
        //     $this->columnField->label($field->getLabel());
        // }
    }

    public static function make(FieldInterface $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
