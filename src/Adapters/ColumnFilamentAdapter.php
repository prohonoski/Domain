<?php

namespace Proho\Domain\Adapters;

use Filament\Tables\Columns\BadgeColumn;
use Proho\Domain\Enums\FieldTypesEnum;
use Filament\Tables\Columns\TextColumn;
use OpenSpout\Common\Entity\Comment\TextRun;
use Proho\Domain\Columns\BadgeColumn as ColumnsBadgeColumn;
use Proho\Domain\Columns\BadgeColumnAdapter;
use Proho\Domain\Columns\BooleanColumnAdapter;
use Proho\Domain\Columns\TextColumnAdapter;
use Proho\Domain\Columns\TextDateColumnAdapter;
use Proho\Domain\Columns\TextDateTimeColumnAdapter;
use Proho\Domain\Columns\TextDecimalColumnAdapter;
use Proho\Domain\Columns\TextHourQtyColumnAdapter;
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
                => ($this->columnField = TextColumnAdapter::make($field)),
            FieldTypesEnum::Radio => ($this->columnField = app(
                BadgeColumnInterface::class,
            )->make($field)),
            FieldTypesEnum::HourQty
                => ($this->columnField = TextHourQtyColumnAdapter::make(
                $field,
            )),
            default => dd([
                "Campo sem correspondencia",
                $field->getType(),
                $field->getName(),
            ]),
        };

        $this->columnField
            ->toggleable($field->isToggleable())
            ->searchable($field->isSearchable())
            ->sortable($field->isSortable())
            ->toggledHiddenByDefault($field->isToggleableHiddenByDefault());
    }

    public static function make(FieldInterface $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
