<?php

namespace App\Domain\Base;

use App\Domain\Base\Adapters\RulesFilamentAdapter;

class Input
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

    public function getForm(): array
    {
        return $this->getComponents();
    }

    public function applyRules(Rules ...$rules): void
    {
        // dd(...$rules);

        foreach ($rules as $key => $rule) {
            foreach ($rule->getRules() as $keyR => $valueR) {
                if (isset($this->components[$keyR])) {
                    $this->components[$keyR] = RulesFilamentAdapter::make(
                        $this->components[$keyR],
                        $valueR
                    );
                }
            }
        }
    }
}
