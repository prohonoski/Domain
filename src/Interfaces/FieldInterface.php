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
    public function isSortable(): bool;
    public function isSearchable(): bool;
    public function isToggleable(): bool;
    public function isToggleableHiddenByDefault(): bool;
    public function isWrap(): bool;
    public function getRelation(): string|array|null;
    public function setDefault(string $default): self;
    public function getDefault(): ?string;
    public function setVisible(bool $state): self;
    public function isVisible(): bool;
}
