<?php

namespace Proho\Domain;

#[\Attribute]
class Validate
{
    public function __construct(public string $rule) {}
}
