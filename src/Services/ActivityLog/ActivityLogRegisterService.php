<?php

namespace Proho\Domain\Services\ActivityLog;


use Proho\Domain\Services\ActivityLog\ORM\ActivityLogEntity;
use Proho\Domain\Services\ActivityLog\ORM\ActivityLogRepository;
use Proho\Domain\Services\ActivityLog\Validators\ActivityLogValidator;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\BaseService;
use Illuminate\Support\Str;

class ActivityLogRegisterService extends BaseService
{
    public function __construct(
        protected ActivityLogRepository $repository,
        protected ActivityLogValidator $validator,
        protected string $description,
        protected string $event,
        protected ?object $subject = null,
        protected ?object $causer = null,
        protected ?array $properties = null,
        protected ?string $batchUuid = null,
    ) {
        parent::__construct();

        // Gera batch UUID se não fornecido
        if ($this->batchUuid === null) {
            $this->batchUuid = (string) Str::uuid();
        }
    }

    protected function handle(): self
    {
        $data = [
            "description" => $this->description,
            "event" => $this->event,
        ];

        // Adiciona informações do subject (entidade afetada)
        if ($this->subject !== null) {
            $data["subject_type"] = get_class($this->subject);
            $data["subject_id"] = method_exists($this->subject, "getId")
                ? $this->subject->getId()
                : null;
        }

        // Adiciona informações do causer (usuário que causou a ação)
        if ($this->causer !== null) {
            $data["causer_type"] = get_class($this->causer);
            $data["causer_id"] = method_exists($this->causer, "getId")
                ? $this->causer->getId()
                : null;
            if (!$data["causer_id"] && $this->causer->id) {
                $data["causer_id"] = $this->causer->id;
            }
        }

        // Adiciona propriedades adicionais como JSON
        if ($this->properties !== null) {
            $data["properties"] = json_encode($this->properties);
        }

        // Adiciona batch UUID
        $data["batch_uuid"] = $this->batchUuid;

        // Valida os dados
        $this->validator->validateForCreate($data);

        // Cria a entidade de log
        $activityLog = $this->repository->fill($data, new ActivityLogEntity());

        // Persiste no banco
        EntityManager::persist($activityLog);

        $this->addSuccess([
            "context" => [
                "id" => "new",
                "record" => $activityLog,
                "message" => "Activity log registrado com sucesso",
            ],
        ]);

        return $this;
    }

    /**
     * Define um novo batch UUID para agrupar logs
     */
    public function withBatchUuid(string $batchUuid): self
    {
        $this->batchUuid = $batchUuid;
        return $this;
    }

    /**
     * Define propriedades adicionais para o log
     */
    public function withProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Define o subject (entidade afetada)
     */
    public function withSubject(?object $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Define o causer (usuário que causou a ação)
     */
    public function withCauser(?object $causer): self
    {
        $this->causer = $causer;
        return $this;
    }
}
