<?php

namespace Proho\Domain\Traits;

use Proho\Domain\Services\ActivityLog\ActivityLogOrch;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasActivityLog
 *
 * Adiciona capacidade de registrar logs de atividade em Services
 * que herdam de BaseSaveService ou outros BaseServices
 */
trait HasActivityLog
{
    protected ?ActivityLogOrch $activityLogOrch = null;
    protected bool $enableActivityLog = true;
    protected ?string $activityLogDescription = "Log";
    protected ?string $activityLogEvent = null;
    protected ?array $activityLogProperties = null;

    /**
     * Inicializa o ActivityLogOrch
     */
    protected function initActivityLog(): void
    {
        if ($this->activityLogOrch === null) {
            $this->activityLogOrch = app(ActivityLogOrch::class);

            // Aplica as configurações do service ao orch
            if (property_exists($this, "dryRun") && $this->dryRun) {
                $this->activityLogOrch->dryRun(true);
            }

            if (
                property_exists($this, "useTransaction") &&
                $this->useTransaction
            ) {
                $this->activityLogOrch->withTransaction();
            }

            if (property_exists($this, "useFlush") && $this->useFlush) {
                $this->activityLogOrch->withFlush();
            }
        }
    }

    /**
     * Registra um log de atividade
     */
    protected function logActivity(
        string $description,
        string $event,
        ?object $subject = null,
        ?object $causer = null,
        ?array $properties = null,
    ): void {
        if (!$this->enableActivityLog) {
            return;
        }

        $this->initActivityLog();

        // Usa o causer fornecido ou tenta pegar o usuário autenticado
        $causer = $causer ?? Auth::user();

        $this->activityLogOrch->log(
            $description,
            $event,
            $subject,
            $causer,
            $properties,
        );
    }

    /**
     * Registra automaticamente o log após a execução do service
     */
    protected function autoLogActivity(?object $record = null): void
    {
        if (!$this->enableActivityLog || !$this->activityLogDescription) {
            return;
        }

        $event = $this->activityLogEvent ?? $this->determineEvent();
        $causer = Auth::user();

        $this->logActivity(
            $this->activityLogDescription,
            $event,
            $record,
            $causer,
            $this->activityLogProperties,
        );
    }

    /**
     * Determina o evento baseado no tipo de service
     */
    protected function determineEvent(): string
    {
        $className = class_basename($this);

        if (str_contains($className, "Create")) {
            return "created";
        }

        if (
            str_contains($className, "Update") ||
            str_contains($className, "Save")
        ) {
            return "updated";
        }

        if (str_contains($className, "Delete")) {
            return "deleted";
        }

        return "action";
    }

    /**
     * Habilita o registro de activity log
     */
    public function withActivityLog(
        string $description,
        ?string $event = null,
        ?array $properties = null,
    ): self {
        $this->enableActivityLog = true;
        $this->activityLogDescription = $description;
        $this->activityLogEvent = $event;
        $this->activityLogProperties = $properties;
        return $this;
    }

    /**
     * Desabilita o registro de activity log
     */
    public function withoutActivityLog(): self
    {
        $this->enableActivityLog = false;
        return $this;
    }

    /**
     * Define um batch UUID para agrupar logs
     */
    public function withActivityLogBatch(?string $batchUuid = null): self
    {
        $this->initActivityLog();
        $this->activityLogOrch->withBatchUuid($batchUuid);
        return $this;
    }

    /**
     * Retorna o ActivityLogOrch
     */
    public function getActivityLogOrch(): ?ActivityLogOrch
    {
        return $this->activityLogOrch;
    }

    /**
     * Retorna o batch UUID do activity log
     */
    public function getActivityLogBatchUuid(): ?string
    {
        return $this->activityLogOrch?->getBatchUuid();
    }
}
