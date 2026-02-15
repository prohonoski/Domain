<?php


namespace Proho\Domain\Services\ActivityLog;

use App\ORM\Entities\Sistema\ActivityLogEntity;
use App\ORM\Repositories\Sistema\ActivityLogRepository;
use Proho\Domain\Services\ActivityLog\ActivityLogRegisterService;
use Proho\Domain\Services\ActivityLog\Validators\ActivityLogValidator;
use Proho\Domain\BaseOrch;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Illuminate\Support\Str;

/**
 * @property ActivityLogRepository $repository
 * @property ActivityLogValidator $validator
 */
class ActivityLogOrch extends BaseOrch
{
    protected ?string $batchUuid = null;

    public function __construct(
        ?ActivityLogRepository $repository = null,
        ?ActivityLogValidator $validator = null,
    ) {
        parent::__construct(
            $repository ??
                EntityManager::getRepository(ActivityLogEntity::class),
            $validator ?? new ActivityLogValidator(),
        );
    }

    /**
     * Registra um novo log de atividade
     *
     * @param string $description Descrição da ação
     * @param string $event Tipo de evento (created, updated, deleted, etc)
     * @param object|null $subject Entidade afetada pela ação
     * @param object|null $causer Usuário que causou a ação
     * @param array|null $properties Propriedades adicionais
     * @return self
     */
    public function log(
        string $description,
        string $event,
        ?object $subject = null,
        ?object $causer = null,
        ?array $properties = null,
    ): self {
        $service = app(ActivityLogRegisterService::class, [
            "repository" => $this->repository,
            "validator" => $this->validator,
            "description" => $description,
            "event" => $event,
            "subject" => $subject,
            "causer" => $causer,
            "properties" => $properties,
            "batchUuid" => $this->batchUuid,
        ]);

        // Configura o service com as opções do orchestrator
        if ($this->dryRun) {
            $service->dryRun(true);
        }

        if ($this->useTransaction) {
            $service->withTransaction();
        }

        $this->runService($service);

        return $this;
    }

    /**
     * Registra log de criação
     */
    public function logCreated(
        string $description,
        ?object $subject = null,
        ?object $causer = null,
        ?array $properties = null,
    ): self {
        return $this->log(
            $description,
            "created",
            $subject,
            $causer,
            $properties,
        );
    }

    /**
     * Registra log de atualização
     */
    public function logUpdated(
        string $description,
        ?object $subject = null,
        ?object $causer = null,
        ?array $properties = null,
    ): self {
        return $this->log(
            $description,
            "updated",
            $subject,
            $causer,
            $properties,
        );
    }

    /**
     * Registra log de exclusão
     */
    public function logDeleted(
        string $description,
        ?object $subject = null,
        ?object $causer = null,
        ?array $properties = null,
    ): self {
        return $this->log(
            $description,
            "deleted",
            $subject,
            $causer,
            $properties,
        );
    }

    /**
     * Define um batch UUID para agrupar múltiplos logs
     */
    public function withBatchUuid(?string $batchUuid = null): self
    {
        $this->batchUuid = $batchUuid ?? (string) Str::uuid();
        return $this;
    }

    /**
     * Retorna o batch UUID atual
     */
    public function getBatchUuid(): ?string
    {
        return $this->batchUuid;
    }

    /**
     * Busca logs por subject
     */
    public function getLogsBySubject(string $subjectType, int $subjectId): array
    {
        return $this->repository->findBySubject($subjectType, $subjectId);
    }

    /**
     * Busca logs por causer
     */
    public function getLogsByCauser(string $causerType, int $causerId): array
    {
        return $this->repository->findByCauser($causerType, $causerId);
    }

    /**
     * Busca logs por evento
     */
    public function getLogsByEvent(string $event): array
    {
        return $this->repository->findByEvent($event);
    }

    /**
     * Busca logs por batch
     */
    public function getLogsByBatch(string $batchUuid): array
    {
        return $this->repository->findByBatch($batchUuid);
    }
}
