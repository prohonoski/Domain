<?php

namespace Proho\Domain\Attributes;

#[\Attribute]
class Rule
{
    public function __construct(public string $rule) {}
}
