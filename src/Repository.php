<?php

namespace Proho\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Proho\Domain\Interfaces\NotificationInterface;
use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use ReflectionClass;

class Repository extends EntityRepository
{
    protected ValidatorInterface $validator;
    protected string $notifyType = "default";
    protected NotificationInterface $notificator;

    public function notifyType(): string
    {
        return $this->notifyType;
    }

    public function execute(): self
    {
        $this->notificator = app(NotificationInterface::class);

        // $this->setValidator(
        //     ...[
        //         $this->dm->getValidator(),
        //         ...$this->nextService ? $this->nextService->getValidator() : [],
        //     ]
        // );

        // if ($this instanceof InstrutorBatchUpdate) {
        //     dd($this->notifyType);
        // }

        // Log::debug("notifiy  type " . $this->notifyType);

        if ($this->notifyType != "none" && $this->notifyType != "parent") {
            // if ($this instanceof PessoaBatchUpdate) {
            //     dd($this->getValidator());
            // }
            //
            //

            match ($this->notifyType) {
                // "success" => $this->notificator->notifyValidatorSucess(
                //     ...$this->getValidator()
                // ),
                // "ifFail" => $this->anyValidatorFail()
                //     ? $this->notificator->notifyValidatorFail(
                //         ...$this->getValidator()
                //     )
                //     : $this->notificator->notifyValidatorSucess(
                //         ...$this->getValidator()
                //     ),
                "oneSuccess" => $this->notificator->notifyValidatorOneSuccess(
                    $this->validator
                ),
                default => $this->notificator->notifyValidatorDefault(
                    $this->validator
                ),
            };
        }
        return $this;
    }

    public function validate(array $data, array $fieldRules = []): self
    {
        $this->validator = $this->validator ?? app(ValidatorInterface::class);

        if (!$fieldRules) {
            $fieldRules = $this->getEntityRules();
        }

        $this->validator->validate($data, $fieldRules);

        // if ($this instanceof DisponibilidadeModel) {
        //        dd($this->validator->messagesAll());
        // }

        return $this;
    }

    public function getEntityRules()
    {
        $mapRule = null;

        $refl = new ReflectionClass($this->getEntityName());

        foreach ($refl->getProperties() as $propriedade) {
            $generated = [];
            $atributosDaPropriedade = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\GeneratedValue::class
            );

            foreach ($atributosDaPropriedade as $atributo) {
                $generated = $atributo->getArguments();
            }

            $atributosDaPropriedade = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class
            );

            foreach ($atributosDaPropriedade as $atributo) {
                $mapColumn = $atributo->newInstance();
            }

            if (
                $mapColumn->nullable === false &&
                !isset($generated["strategy"])
            ) {
                $mapRule[$propriedade->getName()][] = "required";
            }

            //Mostra o nome da propriedade
            $atributosDaPropriedade = $propriedade->getAttributes(
                \Proho\Domain\Attributes\Rule::class
            );

            foreach ($atributosDaPropriedade as $atributo) {
                $mapRule[
                    $propriedade->getName()
                ][] = $atributo->newInstance()->rule;
            }
        }
        return $mapRule;
    }

    public function fill(array $data, mixed $sm = null): mixed
    {
        //

        $classe = $this->getEntityName();

        $sm = $sm ?? new $classe();
        foreach ($data as $key => $field) {
            $method = $this->snakeToPascalCase("set" . $key);

            if (method_exists($sm, $method)) {
                $sm->$method($field);
            }
        }
        return $sm;
    }

    private function snakeToPascalCase(string $string): string
    {
        // Remove os underlines e coloca a primeira letra de cada palavra em maiúscula
        $words = explode("_", $string);
        $capitalizedWords = array_map("ucwords", $words);

        // Junta as palavras capitalizadas em uma única string
        return implode("", $capitalizedWords);
    }

    public function service(
        string $class,
        ?ServiceRepositoryInterface $parentService = null,
        mixed $params = []
    ): ServiceRepositoryInterface {
        $service = app($class, [
            "parent" => $this,
            "params" => $params,
        ]);

        if (!$service->anyValidatorFail()) {
            $this->getEntityManager()->flush();
        }

        return $service;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->getEntityManager();
    }
}
