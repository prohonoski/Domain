<?php

namespace Proho\Domain\Adapters;

use Filament\Tables\Columns\Column;
use Proho\Domain\Enums\FieldTypesEnum;
use Proho\Domain\Columns\BooleanColumnAdapter;
use Proho\Domain\Columns\JsonColumnAdapter;
use Proho\Domain\Columns\SelectColumnAdapter;
use Proho\Domain\Columns\TextColumnAdapter;
use Proho\Domain\Columns\TextDateColumnAdapter;
use Proho\Domain\Columns\TextDateTimeColumnAdapter;
use Proho\Domain\Columns\TextHourQtyColumnAdapter;
use Proho\Domain\Columns\TextNumericColumnAdapter;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class ColumnFilamentAdapter
{
    private $columnField;

    public function getColumnField(): Column
    {
        return $this->columnField;
    }

    function __construct(FieldInterface $field)
    {
        $this->columnField = null;

        // if ($field->getType() == FieldTypesEnum::Radio) {
        //     dd($field);
        // }

        match ($field->getType()) {
            FieldTypesEnum::String
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::StringLong
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::TextArea
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::Decimal
                => ($this->columnField = TextNumericColumnAdapter::make(
                $field,
            )),
            FieldTypesEnum::Integer
                => ($this->columnField = TextNumericColumnAdapter::make(
                $field,
            )),
            FieldTypesEnum::DateTime
                => ($this->columnField = TextDateTimeColumnAdapter::make(
                $field,
            )),
            FieldTypesEnum::Date
                => ($this->columnField = TextDateColumnAdapter::make($field)),
            FieldTypesEnum::Boolean
                => ($this->columnField = BooleanColumnAdapter::make($field)),

            FieldTypesEnum::Select
                => ( ($field?->getColumnAttr()[0] ?? null)?->getArguments()["enumType"] ?? null) ?  $this->columnField = (app(
                                     BadgeColumnInterface::class,
                                 )->make($field)) : $this->columnField = SelectColumnAdapter::make($field),

            FieldTypesEnum::Radio => ($this->columnField = app(
                BadgeColumnInterface::class,
            )->make($field)),
            FieldTypesEnum::HourQty
                => ($this->columnField = TextHourQtyColumnAdapter::make(
                $field,
            )),
            FieldTypesEnum::Json
                => ($this->columnField = JsonColumnAdapter::make(
                $field,
            )),
            default => dd([
                "Campo sem correspondencia",
                $field->getType(),
                $field->getName(),
            ]),
        };

        $toggeable = $field->isToggleable();
        $toggleableHiddenByDefault = $field->isToggleableHiddenByDefault();

        if ($field->getName() == "id") {
            $toggeable = true;
            $toggleableHiddenByDefault = true;
        }

        $this->columnField
            ->toggleable($toggeable)
            ->searchable($field->isSearchable())
            ->sortable($field->isSortable())
            ->toggledHiddenByDefault($toggleableHiddenByDefault);
    }

    public static function make(FieldInterface $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
