<?php

namespace Proho\Domain\Interfaces;

use Proho\Domain\Rules;

interface DomainModelInterface
{
    public function getFields(): array;
    public function getValidator(): ValidatorInterface;
    public function validate(array $data, array $fieldRules = []): self;
    public function get(): self;
    public function records(): array;
    public function record(): array;
    public function batchUpdate(array $dataRows, array $keys): bool;
    public function batchUpdateInsert(array $dataRows, array $keys): bool;
    public function getFieldRules(): Rules;
    public function distinct(array $fields): self;
    public function where(
        string $field,
        string|null $cond = null,
        mixed $value = null,
    ): self;
    public function whereIn(string|null $field, array $value): self;
    public function first(): self;
}
