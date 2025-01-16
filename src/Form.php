<?php

namespace Proho\Domain;

use Proho\Domain\Adapters\RulesFilamentAdapter;
use Proho\Domain\Interfaces\DomainModelInterface;
use Proho\Domain\Interfaces\InputInterface;

use Exception;

class Form
{
    protected $components = [];
    protected $inputInterface;
    protected DomainModelInterface $Dmodel;
    protected string|null $classModel;

    public function __construct()
    {
        $this->configure();
        $this->setUp();
    }

    public static function make(): static
    {
        $static = app(static::class);

        return $static;
    }

    public function configure(string|null $classModel = null): self
    {
        try {
            $class = $classModel ?? ($this->classModel ?? null);

            if ($class) {
                $this->Dmodel = $class::make();
            }
        } catch (Exception $e) {
            throw new Exception(
                "Model Class para formulário não definido" . $e->getMessage()
            );
        }
        return $this;
    }
    /**
     * Cria os componentes conforme as definições do DomainModel
     *
     * Utiliza o DomainModel previamente associado e cria os componentes setados para Fillable.
     * A chave do array de retorno é o nome do campo
     *
     * @return array<string, Field>
     */
    public function autoForm(): array
    {
        $components = [];

        foreach ($this->Dmodel->getFields() as $key => $field) {
            if (!$field->isFillable()) {
                continue;
            }

            $components[$key] = app(InputInterface::class, [
                "field" => $field,
            ])->getInputField();
        }
        return $components;
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
