<?php

namespace Proho\Domain;

use Proho\Domain\Enums\FieldTypesEnum;
use Proho\Domain\Interfaces\FieldInterface;

#[\Attribute]
class Component implements FieldInterface
{
    public function __construct(
        private FieldTypesEnum $type,
        private ?bool $fill = null,
        private ?bool $visible = null,
        private ?string $label = null,
        private ?string $name = null,
        private ?string $hint = null,
        private ?string $hintType = null,
        private ?array $columnAttr = null,
        private ?bool $sortable = false,
        private ?bool $disabled = false,
        private ?bool $toggleable = false,
        private ?bool $toggledHiddenByDefault = false,
        private ?bool $wrap = false,
        private ?bool $searchable = false,
        private ?array $options = null,
        private ?array $relationship = null,
        private ?string $default = null,
    ) {
        $this->fill = $fill ?? true;
        $this->visible = $visible ?? true;
    }

    public function getType(): FieldTypesEnum
    {
        return $this->type;
    }

    public function setLabel(string $name): self
    {
        $this->label = $name;
        return $this;
    }

    public function setColumnAttr(array $column): self
    {
        $this->columnAttr = $column;
        return $this;
    }

    public function getColumnAttr(): array|null
    {
        return $this->columnAttr ?? [];
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function isFillable(): bool
    {
        return $this->fill;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setVisible(bool $state): self
    {
        $this->visible = $state;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function disabled(bool $state): self
    {
        $this->disabled = $state;
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function getLabel(): string|bool|null
    {
        return $this->label;
    }

    public function setDefault(string $name): self
    {
        $this->default = $name;
        return $this;
    }

    public function getDefault(): string|null
    {
        return $this->default ?? null;
    }

    public function setHint(string $name): self
    {
        $this->hint = $name;
        return $this;
    }

    public function getHint(): string|null
    {
        return $this->hint ?? null;
    }

    public function getHintType(): string
    {
        return $this->hintType ?? "float";
    }

    public function getDatalist(): string|array|null
    {
        return $this->datalist ?? null;
    }

    public function getOptions(): string|array|null|Service
    {
        return $this->options ?? null;
    }

    public function getRelation(): string|array|null
    {
        return $this->relationship ?? null;
    }

    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function isToggleableHiddenByDefault(): bool
    {
        return $this->toggledHiddenByDefault;
    }

    public function isWrap(): bool
    {
        return $this->wrap;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }
}
