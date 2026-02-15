<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\TextColumn;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Interfaces\TextColumnInterface;

class JsonColumnAdapter implements TextColumnInterface
{
    public static function make(FieldInterface $field)
    {
        return TextColumn::make($field->getName())
            ->label($field->getLabel())
            ->formatStateUsing(function ($state) {
                if (empty($state)) {
                    return "-";
                }
                $count = is_array($state) ? count($state) : 0;
                return "{$count} item(s)";
            });
    }
}
