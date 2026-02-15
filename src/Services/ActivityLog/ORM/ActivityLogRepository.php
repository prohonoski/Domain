<?php

namespace Proho\Domain\Services\ActivityLog\ORM;

use Proho\Domain\Repository;

class ActivityLogRepository extends Repository
{
    /**
     * Busca logs por subject (entidade relacionada)
     */
    public function findBySubject(string $subjectType, int $subjectId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.subject_type = :subjectType')
            ->andWhere('a.subject_id = :subjectId')
            ->setParameter('subjectType', $subjectType)
            ->setParameter('subjectId', $subjectId)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca logs por causer (usuário que causou a ação)
     */
    public function findByCauser(string $causerType, int $causerId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.causer_type = :causerType')
            ->andWhere('a.causer_id = :causerId')
            ->setParameter('causerType', $causerType)
            ->setParameter('causerId', $causerId)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca logs por evento
     */
    public function findByEvent(string $event): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.event = :event')
            ->setParameter('event', $event)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca logs por batch UUID
     */
    public function findByBatch(string $batchUuid): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.batch_uuid = :batchUuid')
            ->setParameter('batchUuid', $batchUuid)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
