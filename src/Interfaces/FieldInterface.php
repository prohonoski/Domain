<?php

namespace Proho\Domain\Interfaces;

use Proho\Domain\Enums\FieldTypesEnum;

interface FieldInterface
{
    // public function getComponent(): ComponentInterface;
    // public function getColumn(): ColumnInterface;
    // public function getField(): self;
    public function getLabel(): string|bool|null;
    public function getType(): FieldTypesEnum;
    public function getName(): ?string;
}
