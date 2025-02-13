<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Interfaces\TextColumnInterface;

class TextDateColumnAdapter implements TextColumnInterface
{
    public static function make(FieldInterface $field)
    {
        return TextColumn::make($field->getName())
            ->dateTime("d/m/Y")
            ->label($field->getLabel())
            ->alignRight();
    }
}
