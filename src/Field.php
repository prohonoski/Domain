<?php

namespace Proho\Domain;

use Proho\Domain\Enums\FieldTypesEnum;
use Proho\Domain\Interfaces\FieldInterface;

class Field implements FieldInterface
{
    private bool $pk;
    private bool $fill;
    private string|bool $label;
    private string $name;
    private FieldTypesEnum $type;
    public ?bool $sortable = false;
    public ?bool $disabled = false;
    public ?bool $searchable = false;
    public ?bool $toggleable = false;
    public ?bool $toggledHiddenByDefault = false;
    public ?bool $wrap = false;
    public ?array $relationship = null;

    private array $defaultAttrs = [
        "pk" => false,
        "fill" => false,
        "label" => "",
        "name" => "",
        "type" => FieldTypesEnum::String,
    ];

    public function getColumnAttr(): array
    {
        return [];
    }

    public function getDatalist(): string|array|null
    {
        return $this->datalist ?? null;
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
        return $this->hint_type ?? "default";
    }

    public function getPk(): bool
    {
        return $this->pk;
    }

    public function isFillable(): bool
    {
        return $this->fill;
    }

    public function getLabel(): string|bool
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): string|array|null|Service
    {
        return $this->options ?? null;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
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

    public function getType(): FieldTypesEnum
    {
        return $this->type;
    }

    // public static function make(array $data): self
    // {
    //     //return new Field();
    // }

    // public function __construct(
    //     string $name,
    //     string $type,
    //     string $label = "",
    //     string $column_label = "",
    //     string $input_label = "",
    //     bool $fill = false,
    //     bool $pk = false
    // ) {
    //     $this->name = $name;
    //     return $this;
    // }

    public static function make(array $data): static
    {
        $static = app(static::class, ["model" => $data]);
        $static->configure($data);

        return $static;
    }

    public function configure(array $data)
    {
        $data = array_merge($this->defaultAttrs, $data);

        foreach ($data as $key => $value) {
            $attr = "" . $key;
            $this->$attr = $value;
        }

        $this->setUp();
    }

    protected function setUp(): void {}

    //    private array $options;

    // public function getOptions(): array
    // {
    //     return $this->options;
    // }

    // public function setOptions($array)
    // {
    //     return $this->options = $array;
    // }

    // public function getComponent(): ComponentInterface
    // {
    // }
    // public function getColumn(): ColumnInterface
    // {
    // }
    // public function getField(): self
    // {
    // }

    // public function setName(string $name): self
    // {
    //     $this->name = $name;
    //     return $this;
    // }
}
