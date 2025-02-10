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
        public ?string $hint = null
    ) {
        $this->fill = $fill ?? true;
        $this->visible = $fill ?? true;
    }

    public function getType(): FieldTypesEnum
    {
        return $this->type;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setLabel(string $name): self
    {
        $this->label = $name;
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
}
