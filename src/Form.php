<?php

namespace Proho\Domain;

use Proho\Domain\Adapters\RulesFilamentAdapter;
use Proho\Domain\Interfaces\DomainModelInterface;
use Proho\Domain\Interfaces\InputInterface;
use Proho\Domain\Adapters\ColumnFilamentAdapter;

use Exception;

class Form
{
    protected $components = [];
    protected $inputInterface;
    protected DomainModelInterface $Dmodel;
    protected string|null $classModel;

    public function __construct(?DomainModel $dm = null)
    {
        $class = $dm ?? ($this->classModel ?? null);

        $this->configure($class);
        $this->setUp();
    }

    public static function make(?DomainModel $dm = null): static
    {
        $static = app(static::class, ["dm" => $dm]);

        return $static;
    }

    public function configure(mixed $class = null): self
    {
        try {
            if (is_string($class) and class_exists($class)) {
                $this->Dmodel = $class::make();
            } elseif ($class instanceof DomainModel) {
                $this->Dmodel = $class;
            }
        } catch (Exception $e) {
            throw new Exception(
                "Model Class para formulário não definido" . $e->getMessage()
            );
        }
        return $this;
    }
    /**
     * Cria um formulario com os componentes já definidos e aplica as rules do model
     *
     *
     * @return array<string, Field>
     */
    public function autoForm(): self
    {
        $this->autoComponents();
        $this->applyRules($this->Dmodel->getFieldRules());
        return $this;
    }

    /**
     * Cria uma tabela com os componentes já definidos no model
     *
     *
     * @return self
     */
    public function autoTable(): self
    {
        $fields = $this->Dmodel->getFields();

        foreach ($fields as $key => $field) {
            if (!$field->isFillable()) {
                continue;
            }

            $this->components[$key] = ColumnFilamentAdapter::make(
                $field
            )->getColumnField();
        }

        // $this->components["id"] = ColumnFilamentAdapter::make(
        //     $fields["id"]
        // )->getColumnField();
        return $this;
    }

    /**
     * Cria os componentes conforme as definições do DomainModel
     *
     * Utiliza o DomainModel previamente associado e cria os componentes setados para Fillable.
     * A chave do array de retorno é o nome do campo
     *
     * @return self
     */
    public function autoComponents(): self
    {
        $this->components = [];

        foreach ($this->Dmodel->getFields() as $key => $field) {
            if (!$field->isFillable()) {
                continue;
            }

            $this->components[$key] = app(InputInterface::class, [
                "field" => $field,
            ])->getInputField();
        }
        return $this;
    }

    protected function setUp(): void {}

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getForm(): array
    {
        return $this->getComponents();
    }

    public function get(): array
    {
        return $this->components;
    }

    public function applyRules(Rules ...$rules): self
    {
        //dd($rules);

        foreach ($rules as $key => $rule) {
            foreach ($rule->getRules() as $keyR => $valueR) {
                if (isset($this->components[$keyR])) {
                    //dd($valueR);
                    $this->components[$keyR] = RulesFilamentAdapter::make(
                        $this->components[$keyR],
                        $valueR
                    );
                }
            }
        }
        return $this;
    }
}
