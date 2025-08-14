<?php

namespace Proho\Domain;

class Rules
{
    protected array $rules = [];

    public static function make($rules = []): static
    {
        $static = app(static::class);
        $static->configure($rules);

        return $static;
    }

    public function configure($rules = [])
    {
        foreach ($rules as $key => $rule) {
            $this->rules[$key] = $rule;
        }

        $this->setUp();
    }

    protected function setUp(): void {}

    public function getRules(): array
    {
        return $this->rules;
    }
}
