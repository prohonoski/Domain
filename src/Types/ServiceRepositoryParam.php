<?php

namespace Proho\Domain\Types;

class ServiceRepositoryParam
{
    public function __construct(
        public array $dataRows = [],
        public array $params = [],
        public array $rules = []
    ) {}

    public static function make(
        array $dataRows = [],
        array $params = [],
        array $rules = []
    ) {
        $static = new ServiceRepositoryParam($dataRows, $params, $rules);
        return $static;
    }
}
