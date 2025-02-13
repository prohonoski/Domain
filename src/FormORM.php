<?php

namespace Proho\Domain;

use Proho\Domain\Adapters\RulesFilamentAdapter;

use Proho\Domain\Interfaces\InputInterface;
use Proho\Domain\Adapters\ColumnFilamentAdapter;
use ReflectionClass;

use LaravelDoctrine\ORM\Facades\EntityManager;

class FormORM
{
    protected $components = [];
    protected $inputInterface;

    public function __construct(public string $entity)
    {
        $this->setup();
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

        $this->applyRules(
            Rules::make(
                EntityManager::getRepository($this->entity)->getEntityRules()
            )
        );

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
        foreach ($this->getFields() as $propriedade) {
            $ORMColumnAttributes = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class
            );

            //Pega os atributos dessa propriedade em especifico
            $atributosDaPropriedade = $propriedade->getAttributes(
                Component::class
            );
            $field = null;

            foreach ($atributosDaPropriedade as $atributo) {
                if (
                    !isset($atributo->getArguments()["fill"]) ||
                    !$atributo->getArguments()["fill"]
                ) {
                    continue;
                }

                $field = $atributo->newInstance();
                $field->setColumnAttr($ORMColumnAttributes);
                $field->setName($field->getName() ?? $propriedade->getName());
                $field->setLabel($field->getLabel() ?? $propriedade->getName());
            }

            // if (!$field->isFillable()) {
            //     continue;
            // }

            if ($field) {
                $this->components[
                    $field->getName()
                ] = ColumnFilamentAdapter::make($field)->getColumnField();
            }
        }
        return $this;
    }

    private function getFields()
    {
        $refl = new ReflectionClass($this->entity);

        return $refl->getProperties();
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

        foreach ($this->getFields() as $propriedade) {
            //Mostra o nome da propriedade
            $ORMColumnAttributes = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class
            );

            foreach ($ORMColumnAttributes as $atributo) {
                $mapColumn = $atributo->newInstance();
            }
            //Pega os atributos dessa propriedade em especifico
            $atributosDaPropriedade = $propriedade->getAttributes(
                Component::class
            );
            // //Percorre os atributos definidos na propriedade
            foreach ($atributosDaPropriedade as $atributo) {
                $field = $atributo->newInstance();
                $field->setColumnAttr($ORMColumnAttributes);
                $field->setName($field->getName() ?? $propriedade->getName());
                $field->setLabel($field->getLabel() ?? $propriedade->getName());
                $field->setHint(
                    $field->getHint() ?? ($mapColumn->options["comment"] ?? "")
                );

                $this->components[$propriedade->getName()] = app(
                    InputInterface::class,
                    [
                        "field" => $field,
                    ]
                )->getInputField();
            }
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
