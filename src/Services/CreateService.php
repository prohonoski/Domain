<?php

namespace Proho\Domain\Services;

use Proho\Domain\Interfaces\ServiceRepositoryInterface;

use Proho\Domain\ServiceRepository;

class CreateService extends ServiceRepository implements
    ServiceRepositoryInterface
{
    public function execute(): self
    {
        $data = [];

        foreach ($this->params->dataRows as $row) {
            $row["createdAt"] = $row["updatedAt"] ?? now();
            $row["updatedAt"] = $row["updatedAt"] ?? now();
            $data[] = $row;
        }

        $srepo = $this->er;

        $this->validator->validate($data, $srepo->getEntityRules());

        if (!$this->validator->fails()) {
            foreach ($data as $key => $row) {
                $dataEntity = $srepo->fill($row);

                //dd($dataEntity);
                $srepo->getEm()->persist($dataEntity);
            }
        }

        return $this;
    }
}
