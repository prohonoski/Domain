<?php

namespace Proho\Domain\Components;

use Filament\Forms\Components\Wizard;

class WizardResult extends Wizard
{
    protected string $view = "proho-domain::components.wizard-result";

    public array $previousLabels = [
        0 => "Voltar",
        1 => "Voltar",
        2 => "Voltar",
        3 => "Voltar",
        4 => "Voltar",
        5 => "Voltar",
        6 => "Voltar",
    ];
    public array $nextLabels = [
        0 => "Próximo",
        1 => "Próximo",
        2 => "Próximo",
        3 => "Próximo",
        4 => "Próximo",
        5 => "Próximo",
        6 => "Próximo",
    ];

    public function previousLabels(array $labels): static
    {
        $this->previousLabels = $labels;
        return $this;
    }

    public function nextLabels(array $labels): static
    {
        $this->nextLabels = $labels;
        return $this;
    }

    public function getPreviousLabels(): array
    {
        return $this->previousLabels;
    }

    public function getNextLabels(): array
    {
        return $this->nextLabels;
    }
}
