<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\IconColumn;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Interfaces\TextColumnInterface;

class BooleanColumnAdapter implements TextColumnInterface
{
    public static function make(FieldInterface $field)
    {
        return IconColumn::make($field->getName())
            ->boolean()
            //->alignRight()
            //->sortable($field->isSortable())
            ->label($field->getLabel());
    }
}
