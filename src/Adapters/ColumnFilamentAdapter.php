<?php

namespace Proho\Domain\Adapters;

use Filament\Tables\Columns\BadgeColumn;
use Proho\Domain\Enums\FieldTypesEnum;
use Filament\Tables\Columns\TextColumn;

use Proho\Domain\Field;
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
            FieldTypesEnum::String => ($this->columnField = TextColumn::make(
                $field->getName()
            )),
            FieldTypesEnum::StringLong
                => ($this->columnField = TextColumn::make($field->getName())),
            FieldTypesEnum::TextArea => ($this->columnField = TextColumn::make(
                $field->getName()
            )),
            FieldTypesEnum::Decimal => ($this->columnField = TextColumn::make(
                $field->getName()
            )
                //->numeric(decimalPlaces: 2)
                ->alignRight()),
            FieldTypesEnum::Integer => ($this->columnField = TextColumn::make(
                $field->getName()
            )
                // ->numeric()
                ->alignRight()),
            FieldTypesEnum::DateTime => ($this->columnField = TextColumn::make(
                $field->getName()
            )
                ->dateTime("d/m/Y H:i:s")
                // ->numeric()
                ->alignRight()),
            FieldTypesEnum::Date => ($this->columnField = TextColumn::make(
                $field->getName()
            )
                ->dateTime("d/m/Y")
                // ->numeric()
                ->alignRight()),
            FieldTypesEnum::Select => ($this->columnField = TextColumn::make(
                $field->getName()
            )),
            FieldTypesEnum::Radio => ($this->columnField = BadgeColumn::make(
                $field->getName()
            )),
            default => dd([
                "Campo sem correspondencia",
                $field->getType(),
                $field->getName(),
            ]),
        };

        if ($this->columnField->getName() == "situacao") {
            foreach ($field->getColumnAttr() as $key => $value) {
                if (isset($value->getArguments()["enumType"])) {
                    //   dd($value->getArguments()["enumType"]);
                    //
                    //

                    $this->columnField
                        ->enum($value->getArguments()["enumType"]::toArray())
                        //->colors(
                        // [
                        //     "primary" => static fn(
                        //         $state
                        //     ): bool => $state == 1 || $state == 4,
                        // ]

                        ->colors(static function ($state) use ($value) {
                            dd("aaa");
                            dd(
                                $value
                                    ->getArguments()
                                    ["enumType"]::getColors(["state" => $state])
                            );

                            return $value
                                ->getArguments()
                                ["enumType"]::getColors($state);
                        });
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
        }

        if ($this->columnField) {
            $this->columnField->label($field->getLabel());
        }
    }

    public static function make(FieldInterface $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
