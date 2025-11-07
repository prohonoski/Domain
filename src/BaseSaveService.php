<?php

namespace Proho\Domain;

use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\Interfaces\ValidInterface;

class BaseSaveService extends BaseService
{
    public function __construct(
        protected EntityManager $em,
        protected Repository $repository,
        protected ValidInterface $validator,
        protected int $id,
        protected array $data,
    ) {
        parent::__construct();
    }
    protected function handle(): self
    {
        $this->validator->validateForUpdate($this->data, $this->id);
        $record = $this->repository->fill(
            $this->data,
            $this->repository->find($this->id),
        );
        $this->em::persist($record);

        $this->addSuccess([
            "context" => [
                "id" => $record->getId(),
                "message" => "Registro salvo",
            ],
        ]);
        return $this;
    }
}
