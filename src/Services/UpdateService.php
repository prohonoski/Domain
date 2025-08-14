<?php

namespace Proho\Domain\Services;

use Proho\Domain\Interfaces\ServiceRepositoryInterface;

use Proho\Domain\ServiceRepository;

class UpdateService extends ServiceRepository implements
    ServiceRepositoryInterface
{
    public function execute(): self
    {
        $data = [];

        foreach ($this->params->dataRows as $row) {
            $row["updated_at"] = $row["updated_at"] ?? now();
            $data[] = $row;
        }

        $srepo = $this->er;

        $this->validator->validate($data, $this->params->rules);

        if (!$this->validator->fails()) {
            foreach ($data as $key => $row) {
                // dd($data);
                $record = $srepo->findOneBy([
                    "id" => $row["id"] ?? 0,
                ]);

                if (!$record) {
                    throw new \Exception("Record not found" . $data["id"] ?? 0);
                }

                $dataEntity = $srepo->fill($row, $record);

                //dd($dataEntity);
                $srepo->getEm()->persist($dataEntity);
            }
        }

        return $this;
    }
}
