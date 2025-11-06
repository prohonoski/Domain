<?php

namespace Proho\Domain;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use Proho\Domain\Interfaces\NotificationInterface;
use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use ReflectionClass;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Mapping\ManyToOne;
use LaravelDoctrine\ORM\Facades\EntityManager;
use ReflectionMethod;
use ReflectionNamedType;
use Doctrine\ORM\QueryBuilder;

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
                    enum_exists($type->getName())
                ) {
                    // Conversão para Enum
                    $enumClass = $type->getName();
                    $enumValue = $enumClass::tryFrom($field);

                    if ($enumValue !== null) {
                        $sm->$method($enumValue);
                    }
                } elseif (
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

                    if ($value) {
                        $sm->$method($value);
                    }
                } elseif (
                    $type instanceof ReflectionNamedType &&
                    !$type->isBuiltin() &&
                    $key == "tipo"
                ) {
                    dd([$key, $type]);
                } else {
                    $sm->$method($field);
                }
            }
        }

        //depois atribui os valores relacionados

        $reflection = new ReflectionClass($sm);

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            // Verifica se é uma relação ManyToOne
            $relationAttr = $property->getAttributes(ManyToOne::class);

            if (!empty($relationAttr)) {
                $relationClass = $relationAttr[0]->getArguments()[
                    "targetEntity"
                ];
                $key = $name . "_id"; // ex: escolaridade_id
                $method = $this->snakeToPascalCase("set_" . $name);
                // $method = "setEscolaridade";

                if (array_key_exists($key, $data) && $data[$key] !== null) {
                    //$related = new $relationClass();

                    $related = EntityManager::find($relationClass, $data[$key]);

                    //dd($sm, $method, method_exists($sm, $method));

                    // Preenche o ID da entidade relacionada
                    if (method_exists($sm, $method)) {
                        $sm->$method($related);

                        /*$idProp = new ReflectionProperty($related, "id");
                        $idProp->setAccessible(true);
                        $idProp->setValue($related, $data[$key]);*/
                    }

                    //                    $this->$name = $related;
                }

                continue;
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
        mixed $id = "id",
        ?array $fields = ["id"],
        ?string $orderBy = null,
    ): array {
        $query = $this->findOptionsQb($id, $fields, $orderBy)->getQuery();
        return $query->getArrayResult();
    }

    public function findOptionsQb(
        mixed $id = "id",
        ?array $fields = ["id"],
        ?string $orderBy = null,
    ): QueryBuilder {
        // Select fields
        //
        if ($fields == ["id"]) {
            $selectFields = ["a"];
        } else {
            $selectFields = ["a.{$id}"];
            foreach ($fields as $field) {
                $selectFields[] = "a.{$field}";
            }
        }

        $orderField = $orderBy ?: $fields[0];

        $qb = $this->createQueryBuilder("a");
        $qb->select($selectFields);
        $qb->orderBy("a.{$orderField}", "ASC");

        // $query = $this->getEm()->createQuery(
        //     "SELECT a FROM " .
        //         $this->getEntityName() .
        //         " a" .
        //         " ORDER BY a." .
        //        ,
        // );
        return $qb;
    }

    /**
     * Verifica de forma performática se uma entidade existe pelo ID.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        // O retorno é convertido para booleano: true se a contagem > 0, senão false.
        return (bool) $this->createQueryBuilder("e")
            ->select("COUNT(e.id)")
            ->where("e.id = :id")
            ->setParameter("id", $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function searchOptions(
        mixed $id,
        array $fields,
        ?string $orderBy = null,
        ?string $search = null,
        int $limit = 50,
    ): array {
        $data = $this->searchOptionsQb($id, $fields, $orderBy, $search, $limit);

        return $this->extractFields(
            $data->getQuery()->getScalarResult(),
            $fields,
            " - ",
        );
    }

    public function searchOptionsQb(
        mixed $id,
        array $fields,
        ?string $orderBy = null,
        ?string $search = null,
        int $limit = 50,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder("a");

        // Select fields
        $selectFields = ["a.{$id}"];
        foreach ($fields as $field) {
            $selectFields[] = "a.{$field}";
        }
        $qb->select($selectFields);

        // Aplicar busca se fornecida
        if ($search !== null && trim($search) !== "") {
            $orX = $qb->expr()->orX();

            foreach ($fields as $index => $field) {
                $paramName = "search_{$index}";
                $orX->add(
                    $qb->expr()->like("LOWER(a.{$field})", ":{$paramName}"),
                );
                $qb->setParameter($paramName, "%" . strtolower($search) . "%");
            }

            $qb->andWhere($orX);
        }

        // Order by
        $orderField = $orderBy ?? $fields[0];
        $qb->orderBy("a.{$orderField}", "ASC");

        // Limit
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
    /**
     * @param array $dados             // Array de entrada
     * @param array $camposConcat      // Lista de campos que serão concatenados
     * @param string $separador        // Separador entre os campos (opcional)
     * @param string $chaveTipo        // Nome do campo final (default: 'tipo')
     * @return array                   // Array reduzido com id e campo concatenado
     */
    function extractFields(
        array $dados,
        array $camposConcat,
        string $separador = " - ",
    ): array {
        $resultado = [];

        foreach ($dados as $item) {
            $valores = array_map(
                fn($campo) => $item[$campo] ?? "",
                $camposConcat,
            );

            $resultado[$item["id"]] = trim(
                implode(
                    $separador,
                    array_filter($valores, fn($v) => $v !== ""),
                ),
            );
        }

        return $resultado;
    }
    /**
     * Verifica se existe duplicidade baseado nos campos fornecidos
     *
     * @param array $fields ['nome' => 'João', 'email' => 'joao@example.com']
     * @param int|null $excludeId ID para excluir da verificação (útil em updates)
     * @return bool
     */
    public function isDuplicated(array $fields, ?int $excludeId = null): bool
    {
        if (empty($fields)) {
            return false;
        }

        $qb = $this->createQueryBuilder("e");

        // Adiciona condições WHERE para cada campo
        foreach ($fields as $field => $value) {
            $paramName = str_replace(".", "_", $field); // Remove pontos se houver

            $qb->andWhere("e.{$field} = :{$paramName}")->setParameter(
                $paramName,
                $value,
            );
        }

        // Exclui o próprio registro se for update
        if ($excludeId !== null) {
            $qb->andWhere("e.id != :excludeId")->setParameter(
                "excludeId",
                $excludeId,
            );
        }
        $qb->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult() !== null;
    }
}
