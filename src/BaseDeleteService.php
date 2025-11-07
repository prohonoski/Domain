<?php

namespace Proho\Domain;

use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\Interfaces\ValidInterface;

class BaseDeleteService extends BaseService
{
    public function __construct(
        protected EntityManager $em,
        protected Repository $repository,
        protected ValidInterface $validator,
        protected int $id,
        protected ?array $data = [],
    ) {
        parent::__construct();
    }
    protected function handle(): self
    {
        $record = $this->repository->find($this->id);

        if ($record) {
            $this->validator->validateForDelete($record->toArray(), $this->id);
            $this->em::remove($record);
        }

        $this->addSuccess([
            "context" => [
                "id" => $record->getId(),
                "message" => "Registro removido",
            ],
        ]);
        return $this;
    }
}
