<?php

namespace Proho\Domain;

use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use App\Models\Sistema\Context;
use App\ORM\Entities\EnsinoExec\SolicitacaoMaterialEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class ServiceRepository implements ServiceRepositoryInterface
{
    protected $result = [];
    protected ValidatorInterface $validator;
    protected ServiceRepositoryInterface $child;
    protected EntityManager $em;

    function __construct(protected $params, protected $parent)
    {
        if ($parent instanceof EntityManager) {
            $this->em = $parent;
        } elseif ($parent instanceof EntityRepository) {
            $this->em = $parent->getEm();
        }

        $this->result = [];
        $this->validator = $this->validator ?? app(ValidatorInterface::class);

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

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getAllValidator(array &$validators = []): array
    {
        if ($this->getChild()) {
            $this->getChild()->getAllValidator($validators);
        }
        $validators[] = $this->getValidator();
        return $validators;
    }

    public function getChild(): ServiceRepositoryInterface|null
    {
        return $this->child ?? null;
    }

    public function anyValidatorFail(
        ServiceRepositoryInterface $service = null
    ): bool {
        if ($service == null) {
            $service = $this;
        }

        if ($service->getValidator()->fails()) {
            return true;
        } elseif ($service->getChild()) {
            if ($this->anyValidatorFail($service->child)) {
                return true;
            }
        }

        return false;
    }
}
