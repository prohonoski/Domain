<?php

namespace Proho\Domain;

use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\Interfaces\ValidInterface;

class BaseCreateService extends BaseService
{
    public function __construct(
        protected EntityManager $em,
        protected Repository $repository,
        protected ValidInterface $validator,
        protected array $data,
    ) {
        parent::__construct();
    }
    protected function handle(): self
    {
        $this->validator->validateForCreate($this->data);
        $record = $this->repository->fill($this->data, null);
        $this->em::persist($record);

        $this->addSuccess([
            "context" => [
                "id" => "new",
                "message" => "Registro criado",
            ],
        ]);
        return $this;
    }
}
