<?php

namespace Proho\Domain\Columns;

use Filament\Tables\Columns\BadgeColumn;
use Proho\Domain\Interfaces\BadgeColumnInterface;
use Proho\Domain\Interfaces\FieldInterface;

class BadgeColumnAdapter implements BadgeColumnInterface
{
    public static function make(FieldInterface $field)
    {
        return BadgeColumn::make($field->getName())->label($field->getLabel());
    }
}
