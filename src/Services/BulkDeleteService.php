<?php

namespace Proho\Domain\Services;

use Proho\Domain\Interfaces\ServiceRepositoryInterface;
use App\ORM\Entities\EnsinoExec\SolicitacaoMaterialEntity;
use Proho\Domain\ServiceRepository;

class BulkDeleteService extends ServiceRepository implements
    ServiceRepositoryInterface
{
    public function execute(): self
    {
        foreach ($this->params->dataRows as $key => $row) {
            $data[] = [
                "id" => $row["id"],
            ];
        }

        // Extraindo os IDs
        $ids = array_map(function ($item) {
            return $item["id"];
        }, $data);

        $qb = $this->em
            ->createQueryBuilder()
            ->delete(SolicitacaoMaterialEntity::class, "sm")
            ->where("sm.id in (:ids)")
            ->setParameter("ids", $data)
            ->getQuery()
            ->execute();

        return $this;
    }
}
