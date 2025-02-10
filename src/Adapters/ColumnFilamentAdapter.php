<?php

namespace Proho\Domain\Adapters;

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
            default => dd([$field->getType(), $field->getName()]),
        };

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
