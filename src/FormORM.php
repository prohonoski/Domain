<?php

namespace Proho\Domain;

use Filament\Tables\Columns\Column;
use Proho\Domain\Adapters\RulesFilamentAdapter;

use Proho\Domain\Interfaces\InputInterface;
use Proho\Domain\Adapters\ColumnFilamentAdapter;
use ReflectionClass;

use LaravelDoctrine\ORM\Facades\EntityManager;

class FormORM
{
    protected $components = [];
    /** @var array<string, Component> */
    protected array $configuredComponents = [];
    protected $inputInterface;

    public function __construct(public string $entity)
    {
        $this->setup();
    }
    public function reOrder(array $order): self
    {
        $itensOrdenados = [];
        foreach ($order as $key) {
            if (isset($this->components[$key])) {
                $itensOrdenados[$key] = $this->components[$key];
            }
        }

        $this->components = $itensOrdenados;
        return $this;
    }
    /**
     * Cria um formulario com os componentes já definidos e aplica as rules do model
     *
     *
     * @return self
     */
    public function autoForm(): self
    {
        $this->autoComponents();

        $this->applyRules(
            Rules::make(
                EntityManager::getRepository($this->entity)->getEntityRules(),
            ),
        );

        return $this;
    }

    public function getProprerties(): array
    {
        foreach ($this->getFields() as $propriedade) {
            $ORMColumnAttributes = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class,
            );
            $return[] = [
                "columnAttr" => $ORMColumnAttributes,
                "attr" => $propriedade->getAttributes(Component::class),
            ];
        }
        return $return;
    }

    /**
     * Cria uma tabela com os componentes já definidos no model
     *
     *
     * @return self
     */
    public function oldautoTable(): self
    {
        foreach ($this->getFields() as $propriedade) {
            $ORMColumnAttributes = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class,
            );

            //Pega os atributos dessa propriedade em especifico
            $atributosDaPropriedade = $propriedade->getAttributes(
                Component::class,
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

    public function old2autoTable(): self
    {
        foreach ($this->getFields() as $propriedade) {
            $confComponent = $this->buildTableComponent($propriedade);
            if ($confComponent) {
                $this->components[
                    $confComponent->getName()
                ] = $this->createComponent($confComponent);
            }
        }

        return $this;
    }

    /**
     * Cria e configura o componente com os dados da propriedade
     */
    public function createComponent(Component $component): Column
    {
        return ColumnFilamentAdapter::make($component)->getColumnField();
    }

    /**
     * Cria e configura o componente com os dados da propriedade
     */
    private function configureComponent(
        \ReflectionAttribute $attribute,
        \ReflectionProperty $propriedade,
    ): Component {
        $component = $attribute->newInstance();

        $ormAttributes = $propriedade->getAttributes(
            \Doctrine\ORM\Mapping\Column::class,
        );

        // dd(
        //     $attribute,
        //     $ormAttributes,
        //     $propriedade
        //         ->getAttributes(\Proho\Domain\Component::class)[0]
        //         ->getArguments(),
        // );

        $component->setColumnAttr($ormAttributes);
        $component->setName($component->getName() ?? $propriedade->getName());
        $component->setLabel($component->getLabel() ?? $propriedade->getName());

        return $component;
    }

    /**
     * Extrai o atributo fillable da propriedade
     */
    private function extractFillableAttribute(
        \ReflectionProperty $propriedade,
    ): ?\ReflectionAttribute {
        $componentAttributes = $propriedade->getAttributes(Component::class);

        foreach ($componentAttributes as $attribute) {
            $args = $attribute->getArguments();

            if (!isset($args["fill"]) || $args["fill"]) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Constrói um componente de tabela a partir de uma propriedade
     */
    public function buildTableComponent(
        \ReflectionProperty $propriedade,
    ): ?Component {
        $fieldAttribute = $this->extractFillableAttribute($propriedade);

        if ($fieldAttribute === null) {
            return null;
        }

        return $this->configureComponent($fieldAttribute, $propriedade);
    }

    public function getFields()
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
                \Doctrine\ORM\Mapping\Column::class,
            );

            foreach ($ORMColumnAttributes as $atributo) {
                $mapColumn = $atributo->newInstance();
            }
            //Pega os atributos dessa propriedade em especifico
            $atributosDaPropriedade = $propriedade->getAttributes(
                Component::class,
            );

            // //Percorre os atributos definidos na propriedade
            //

            foreach ($atributosDaPropriedade as $atributo) {
                $field = $atributo->newInstance();
                $field->setColumnAttr($ORMColumnAttributes);
                $field->setName($field->getName() ?? $propriedade->getName());
                $field->setLabel($field->getLabel() ?? $propriedade->getName());
                $field->setHint(
                    $field->getHint() ?? ($mapColumn->options["comment"] ?? ""),
                );

                $this->components[$propriedade->getName()] = app(
                    InputInterface::class,
                    [
                        "field" => $field,
                    ],
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

    public function setComponents(array $components): self
    {
        $this->components = $components;
        return $this;
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
        foreach ($rules as $key => $rule) {
            foreach ($rule->getRules() as $keyR => $valueR) {
                if (isset($this->components[$keyR])) {
                    //dd($valueR);
                    $this->components[$keyR] = RulesFilamentAdapter::make(
                        $this->components[$keyR],
                        $valueR,
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Novo método: configura todos os componentes da entidade de uma só vez
     * e armazena em $this->configuredComponents
     */
    public function configureAllComponents(): self
    {
        $this->configuredComponents = [];

        foreach ($this->getFields() as $propriedade) {
            $component = $this->buildTableComponent($propriedade);

            if ($component) {
                $this->configuredComponents[$component->getName()] = $component;
            }
        }

        return $this;
    }

    /**
     * Retorna todos os componentes já configurados (executa se ainda não foi feito)
     *
     * @return array<Component>
     */
    public function getConfiguredComponents(): array
    {
        if (empty($this->configuredComponents)) {
            $this->configureAllComponents();
        }

        return $this->configuredComponents;
    }

    /**
     * Configura todos os componentes de uma vez e armazena internamente.
     *
     * @param array<Component>
     */
    public function setConfiguredComponents(array $components): self
    {
        if (!$this->configuredComponents) {
            $this->configureAllComponents();
        }

        $this->configuredComponents = $components;
        return $this;
    }

    /**
     * Cria os componentes da tabela usando os já configurados
     */
    public function autoTable(): self
    {
        $this->components = [];

        foreach ($this->getConfiguredComponents() as $component) {
            $this->components[$component->getName()] = $this->createComponent(
                $component,
            );
        }

        return $this;
    }
}
