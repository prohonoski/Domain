<?php

namespace Proho\Domain\Types;

class ServiceRepositoryParam
{
    public function __construct(
        public array $dataRows = [],
        public array $params = [],
        public array $rules = []
    ) {}
}
