<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Interfaces\TextColumnInterface;

class TextDateTimeColumnAdapter implements TextColumnInterface
{
    public static function make(FieldInterface $field)
    {
        return TextColumn::make($field->getName())
            ->dateTime("d/m/Y H:i:s")
            ->label($field->getLabel())
            ->sortable($field->isSortable())
            ->alignRight();
    }
}
