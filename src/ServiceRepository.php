<?php

namespace Proho\Domain;

use App\Domain\Patrimonio\Almoxarifado\Services\AlmoxarifadoFindOrCreateSipacService;
use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class ServiceRepository implements ServiceRepositoryInterface
{
    protected $result = [];
    protected ValidatorInterface $validator;
    protected array $child;
    protected EntityManager $em;
    protected EntityRepository $er;
    public static string $label = "";
    public static string $description = "";

    function __construct(protected $params, protected $parent)
    {
        if ($parent instanceof EntityManager) {
            $this->em = $parent;
        } elseif ($parent instanceof EntityRepository) {
            $this->em = $parent->getEm();
            $this->er = $parent;
        }

        $this->result = [];
        $this->validator = $this->validator ?? app(ValidatorInterface::class);
        $this->validator->setService($this);

        if (static::$label == "") {
            $pos = strrpos(get_class($this), "\\");
            static::$label = substr(get_class($this), $pos + 1);
        }

        $this->execute();
    }

    public function execute(): self
    {
        return $this;
    }

    public function validate(array $dataRows, array $fieldRules = []): self
    {
        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getLabel(): string
    {
        return static::$label;
    }

    public function getDescription(): string
    {
        return static::$description;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getAllValidator(array &$validators = []): array
    {
        foreach ($this->getChild() as $child) {
            $child->getAllValidator($validators);
        }

        $validators[] = $this->getValidator();
        return $validators;
    }

    /**
     * @return ServiceRepositoryInterface[]
     */
    public function getChild(): array
    {
        return $this->child ?? [];
    }

    public function anyValidatorFail(
        ServiceRepositoryInterface $service = null
    ): bool {
        if ($service == null) {
            $service = $this;
        }

        if ($service->getValidator()->fails()) {
            return true;
        } else {
            $validators = $this->getAllValidator();
            foreach ($validators as $validator) {
                if ($validator->fails()) {
                    return true;
                }
            }
        }

        // if ($service->getChild()) {
        //     if ($this->anyValidatorFail($service->child)) {
        //         return true;
        //     }
        // }

        return false;
    }

    public function addChild(ServiceRepositoryInterface $service): self
    {
        $this->child[] = $service;
        return $this;
    }
}
