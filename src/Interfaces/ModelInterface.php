<?php

namespace Proho\Domain\Interfaces;

interface ModelInterface
{
    //   public static function make(mixed $model): self;
    public function updateInsert(array $data, array $keys);
    public function batchUpdate(array $dataRows, array $keys);
    public function where(
        string $field,
        string|null $cond = null,
        string|null $value = null
    ): self;
    public function getData(): array;
    public function distinct(array $fields = []): self;
    public function whereIn(string|null $field, array $value): self;
    public function get(): self;
    public function first(): self;
}
