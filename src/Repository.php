<?php

namespace Proho\Domain;

use App\ORM\Entities\BaseEntity;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Proho\Domain\Interfaces\NotificationInterface;
use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use ReflectionClass;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use ReflectionMethod;
use ReflectionNamedType;

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
                    $this->validator,
                ),
                default => $this->notificator->notifyValidatorDefault(
                    $this->validator,
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
                \Doctrine\ORM\Mapping\GeneratedValue::class,
            );

            foreach ($atributosDaPropriedade as $atributo) {
                $generated = $atributo->getArguments();
            }

            $atributosDaPropriedade = $propriedade->getAttributes(
                \Doctrine\ORM\Mapping\Column::class,
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
                \Proho\Domain\Attributes\Rule::class,
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
                $refMethod = new ReflectionMethod($sm, $method);
                $type = $refMethod->getParameters()[0]->getType();

                $expected = BaseEntity::class;

                if (
                    $type instanceof ReflectionNamedType &&
                    !$type->isBuiltin() &&
                    ($type->getName() == "DateTimeInterface" ||
                        $type->getName() == "DateTime") &&
                    is_string($field)
                ) {
                    $value = new DateTime($field);
                    if ($value) {
                        $sm->$method($value);
                    }
                } elseif (
                    $type instanceof ReflectionNamedType &&
                    !$type->isBuiltin() &&
                    (is_subclass_of($type->getName(), $expected) ||
                        $type->getName() === $expected)
                ) {
                    $value = EntityManager::getRepository(
                        $type->getName(),
                    )->findOneBy(["id" => $field]);

                    //dd("É ou herda de $expected" . $type->getName());
                    if ($value) {
                        $sm->$method($value);
                    }
                } else {
                    $sm->$method($field);
                }
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
        mixed $params = [],
    ): ServiceRepositoryInterface {
        $service = app($class, [
            "parent" => $this,
            "params" => $params,
        ]);
        if (
            !$service->anyValidatorFail() &&
            ($params->params["flush"] ?? false)
        ) {
            try {
                $this->getEntityManager()->flush();
            } catch (UniqueConstraintViolationException $e) {
                $service->getValidator()->after(function ($validator) use ($e) {
                    $message = $e->getMessage();
                    $error_message = "";

                    // Normaliza a string, remove quebras de linha
                    $message = str_replace(["\r", "\n"], " ", $message);

                    preg_match(
                        '/unique constraint "(.*?)"/i',
                        $message,
                        $constraintMatch,
                    );
                    preg_match(
                        "/Key \((.*?)\)=\((.*?)\)/",
                        $message,
                        $keyMatch,
                    );

                    if ($constraintMatch && $keyMatch) {
                        $constraint = $constraintMatch[1]; // Ex: unique_account_entry
                        $fields = explode(", ", $keyMatch[1]); // Ex: ['entry_id', 'account_id']
                        $values = explode(", ", $keyMatch[2]); // Ex: ['2', '6']

                        // Monta mensagem amigável
                        $error_message = "Violação da restrição '$constraint'. ";
                        $pairs = [];
                        foreach ($fields as $index => $field) {
                            $pairs[] = "$field = " . ($values[$index] ?? "?");
                        }
                        $error_message .=
                            "Valores duplicados: " .
                            implode(", ", $pairs) .
                            ".";
                    } else {
                        $error_message = "Erro de integridade único detectado, mas não foi possível extrair detalhes. Mensagem bruta: $message";
                    }
                    $validator->errors()->add("id", $error_message);
                });
            }
        }
        return $service;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->getEntityManager();
    }

    public function findOptions(
        mixed $id,
        array $fields,
        string $orderBy = null,
    ): array {
        $select_fields = "a." . $id;

        foreach ($fields as $field) {
            if ($select_fields == "") {
                $select_fields .= "a." . $field;
            } else {
                $select_fields .= ", a." . $field;
            }
        }

        $query = $this->getEm()->createQuery(
            "SELECT " .
                $select_fields .
                " FROM " .
                $this->getEntityName() .
                " a" .
                " ORDER BY a." .
                ($orderBy ? $orderBy : $fields[0]),
        );

        //$query->$options = [];
        //
        $options = [];

        foreach ($query->getResult() as $row) {
            $value = "";
            foreach ($fields as $field) {
                if ($value == "") {
                    $value .= $row[$field];
                } else {
                    $value .= ", " . $row[$field];
                }
            }
            $options[$row[$id]] = $value;
        }

        return $options;
    }
}
