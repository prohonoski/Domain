<?php

namespace Proho\Domain\Interfaces;

use Proho\Domain\Enums\FieldTypesEnum;

interface FieldInterface
{
    // public function getComponent(): ComponentInterface;
    // public function getColumn(): ColumnInterface;
    // public function getField(): self;
    public function getType(): FieldTypesEnum;
}
