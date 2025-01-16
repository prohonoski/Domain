<?php

namespace Proho\DomainAdapters;

use Proho\DomainEnums\FieldTypesEnum;
use Proho\DomainField;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ColumnFilamentAdapter
{
    private $columnField;

    public function getColumnField()
    {
        return $this->columnField;
    }

    function __construct(Field $field)
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
                ->numeric(decimalPlaces: 2)
                ->alignRight()),
            FieldTypesEnum::Integer => ($this->columnField = TextColumn::make(
                $field->getName()
            )
                ->numeric()
                ->alignRight()),
        };

        if ($this->columnField) {
            $this->columnField->label($field->getLabel());
        }
    }

    public static function make(Field $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
