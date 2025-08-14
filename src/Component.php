<?php

namespace Proho\Domain;

use Proho\Domain\Enums\FieldTypesEnum;
use Proho\Domain\Interfaces\FieldInterface;

#[\Attribute]
class Component implements FieldInterface
{
    public function __construct(
        public FieldTypesEnum $type,
        public ?bool $fill = null,
        public ?bool $visible = null,
        public ?string $label = null,
        public ?string $name = null,
        public ?string $hint = null,
        public ?array $columnAttr = null,
        public ?bool $sortable = false,
        public ?bool $toggleable = false,
        public ?bool $toggledHiddenByDefault = false,
        public ?bool $wrap = false,
        public ?bool $searchable = false,
        public ?array $options = null,
        public ?array $relation = null
    ) {
        $this->fill = $fill ?? true;
        $this->visible = $fill ?? true;
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
        return $this->columnAttr;
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

    public function getLabel(): string|bool|null
    {
        return $this->label;
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

    public function getDefault(): string|null
    {
        return $this->default ?? null;
    }

    public function getHintType(): string
    {
        return $this->hint_type ?? "float";
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
        return $this->relation ?? null;
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
