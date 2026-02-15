<?php
namespace Proho\Domain\Services\ActivityLog\ORM;


use Doctrine\ORM\Mapping as ORM;
use Proho\Domain;
use Proho\Domain\Enums\FieldTypesEnum;

#[
    ORM\Table(name: "activity_log", schema: "sga"),
    ORM\Index(name: "activity_log_log_name_index", fields: ["log_name"]),
    ORM\Index(name: "activity_log_event_index", fields: ["event"]),
    ORM\Index(name: "activity_log_batch_uuid_index", fields: ["batch_uuid"]),
    ORM\Index(name: "causer", fields: ["causer_type", "causer_id"]),
    ORM\Index(name: "subject", fields: ["subject_type", "subject_id"]),
    ORM\Entity(repositoryClass: ActivityLogRepository::class),
]
#[ORM\HasLifecycleCallbacks]
class ActivityLogEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    protected int $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "Descrição",
        ),
    ]
    private string $log_name;

    #[ORM\Column(type: "text", length: 255)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "Descrição",
        ),
    ]
    private string $description;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "subject_type",
        ),
    ]
    private string $subject_type;

    #[ORM\Column(type: "bigint", nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "subject_id",
        ),
    ]
    private int $subject_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "causer_type",
        ),
    ]
    private string $causer_type;

    #[ORM\Column(type: "bigint", nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "causer_id",
        ),
    ]
    private ?int $causer_id;

    #[ORM\Column(type: "json", nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "properties",
        ),
    ]
    private string $properties;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "event",
        ),
    ]
    private string $event;

    #[ORM\Column(type: "guid", nullable: true)]
    #[
        Domain\Component(
            type: FieldTypesEnum::String,
            fill: true,
            visible: true,
            label: "batch_uuid",
        ),
    ]
    private string $batch_uuid;

    ###### Apenas para nao gerar erro #####
    ###### Qaundo migrar completamente pode ser removido #####

    #[ORM\Column(type: "bigint", nullable: true)]
    protected int $owner_uid;
    #[ORM\Column(type: "json", nullable: true)]
    protected array $owner_gid;

    ###### Apenas para nao gerar erro #####
    ###### Qaundo migrar completamente pode ser removido #####

    public function __construct() {}

    // Métodos Getter e Setter
    public function getId(): int
    {
        return $this->id;
    }

    public function getLogName(): ?string
    {
        return $this->log_name ?? null;
    }

    public function setLogName(?string $log_name): self
    {
        $this->log_name = $log_name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSubjectType(): ?string
    {
        return $this->subject_type ?? null;
    }

    public function setSubjectType(?string $subject_type): self
    {
        $this->subject_type = $subject_type;
        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subject_id ?? null;
    }

    public function setSubjectId(?int $subject_id): self
    {
        $this->subject_id = $subject_id;
        return $this;
    }

    public function getCauserType(): ?string
    {
        return $this->causer_type ?? null;
    }

    public function setCauserType(?string $causer_type): self
    {
        $this->causer_type = $causer_type;
        return $this;
    }

    public function getCauserId(): ?int
    {
        return $this->causer_id ?? null;
    }

    public function setCauserId(?int $causer_id): self
    {
        $this->causer_id = $causer_id;
        return $this;
    }

    public function getProperties(): ?string
    {
        return $this->properties ?? null;
    }

    public function setProperties(?string $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->event ?? null;
    }

    public function setEvent(?string $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getBatchUuid(): ?string
    {
        return $this->batch_uuid ?? null;
    }

    public function setBatchUuid(?string $batch_uuid): self
    {
        $this->batch_uuid = $batch_uuid;
        return $this;
    }
}
