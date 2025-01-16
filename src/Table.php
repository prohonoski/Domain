<?php

namespace Proho\Domain;

class Table
{
    protected $components = [];

    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }

    public function configure()
    {
        $this->setUp();
    }

    protected function setUp(): void
    {
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getColumns(): array
    {
        return $this->getComponents();
    }
}
