<?php

namespace Proho\Domain\Interfaces;

interface DomainServiceInterface
{
    public function query(array $params = []): array;
    public function getValidator(): array;
    public function set(array $data): self;
    public function autoNotify(string $type): self;
    public function execute(): self;
}
