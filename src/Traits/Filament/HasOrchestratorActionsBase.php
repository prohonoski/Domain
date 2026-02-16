<?php

namespace Proho\Domain\Traits\Filament;

use Filament\Notifications\Notification;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\Exceptions\BusinessRuleException;

/**
 * Trait HasOrchestratorActionsBase
 *
 * Contém a lógica comum de integração Orquestrador ↔ Filament.
 * Métodos que dependem de namespaces versionados (Actions, getEditAction, getDeleteAction,
 * getActions/getHeaderActions) ficam nos traits V2/V3.
 */
trait HasOrchestratorActionsBase
{
    /**
     * Executa uma ação do orquestrador e trata exceções de negócio
     */
    protected function executeOrquestratorAction(
        callable $action,
        string $successMessage,
        ?object $filamentAction = null,
    ): void {
        try {
            $action();
            Notification::make()->title($successMessage)->success()->send();
        } catch (BusinessRuleException $e) {
            Notification::make()
                ->title($this->getBusinessErrorMessage())
                ->body($e->getErrorsMessages())
                ->danger()
                ->send();

            $filamentAction?->halt();
        } catch (ValidationException $e) {
            $mensagem = collect($e->errors())
                ->flatMap(
                    fn($msgs, $key) => collect($msgs)->map(
                        fn($msg) => "• $msg ($key)",
                    ),
                )
                ->implode("\n<br>");
            Notification::make()
                ->title($this->getBusinessErrorMessage())
                ->body($mensagem)
                ->danger()
                ->send();
            throw $e;
        }
    }

    /**
     * Gera mensagem de sucesso baseada na ação e no modelo
     */
    protected function getActionSuccessMessage(string $action): string
    {
        $modelLabel = "";
        if (method_exists(static::class, "getModelLabel")) {
            $modelLabel = static::getModelLabel();
        } elseif (method_exists(static::class, "getResource")) {
            $modelLabel = static::getResource()::getModelLabel();
        }

        $modelLabel = ucfirst($modelLabel);

        $result = match ($action) {
            "create" => "$modelLabel criado com sucesso",
            "update" => "$modelLabel alterado com sucesso",
            "delete" => "$modelLabel excluído com sucesso",
            default => "$modelLabel processado com sucesso",
        };
        return mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);
    }

    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    /**
     * Mensagem padrão para erros de regra de negócio
     */
    public function getBusinessErrorMessage(): string
    {
        return "Erro na validação";
    }

    /**
     * Executa ação de criação com tratamento de exceções
     */
    public function executeCreateAction(
        callable $createCallback,
        ?object $action = null,
    ): void {
        $this->executeOrquestratorAction(
            $createCallback,
            $this->getActionSuccessMessage("create"),
            $action,
        );
    }

    /**
     * Executa ação de atualização com tratamento de exceções
     */
    public function executeUpdateAction(
        callable $updateCallback,
        ?object $action = null,
    ): void {
        $this->executeOrquestratorAction(
            $updateCallback,
            $this->getActionSuccessMessage("update"),
            $action,
        );
    }

    /**
     * Executa ação de exclusão com tratamento de exceções
     */
    public function executeDeleteAction(
        callable $deleteCallback,
        ?object $action = null,
    ): void {
        $this->executeOrquestratorAction(
            $deleteCallback,
            $this->getActionSuccessMessage("delete"),
            $action,
        );
    }

    /**
     * Executa ação de exclusão em massa com tratamento de exceções
     */
    public function executeDeleteBulkAction(
        callable $deleteBulkCallback,
        ?object $action = null,
    ): void {
        $modelLabel = "";
        if (method_exists(static::class, "getPluralModelLabel")) {
            $modelLabel = static::getPluralModelLabel();
        } elseif (method_exists(static::class, "getResource")) {
            $modelLabel = static::getResource()::getPluralModelLabel();
        }

        $modelLabel = ucfirst($modelLabel);
        $successMessage =
            mb_strtoupper(mb_substr($modelLabel, 0, 1)) .
            mb_substr($modelLabel, 1) .
            " excluídos com sucesso";

        $this->executeOrquestratorAction(
            $deleteBulkCallback,
            $successMessage,
            $action,
        );
    }

    protected function getDeleteRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl("index");
    }

    /**
     * Monta a lista de ações customizadas baseado no tipo da página.
     * Usado internamente por getActions() (v2) e getHeaderActions() (v3).
     */
    protected function buildPageActions(array $parentActions): array
    {
        $csAction = [];

        if ($this instanceof CreateRecord) {
            $csAction = $this->getActionsCreate();
        } elseif ($this instanceof EditRecord) {
            $csAction = $this->getActionsEdit();
        } elseif ($this instanceof ListRecords) {
            $csAction = $this->getActionsList();

            // Remover a ação criar default do Filament (compatível v2 e v3)
            foreach ($parentActions as $key => $item) {
                if (str_contains(get_class($item), "CreateAction")) {
                    unset($parentActions[$key]);
                    break;
                }
            }
        } elseif ($this instanceof ViewRecord) {
            $csAction = $this->getActionsView();
        }

        return array_merge($csAction);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(static::class)->executeUpdateAction(
            fn() => app(static::class)
                ->getOrchestrator()
                ->update($record->id, $data),
            $this,
        );

        EntityManager::flush();
        return $record;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $updated_at = now();
        $data["updated_at"] = $updated_at;

        app(static::class)->executeCreateAction(
            fn() => app(static::class)->getOrchestrator()->create($data),
            $this,
        );
        EntityManager::flush();

        return static::getResource()
            ::getModel()
            ::where(
                "id",
                app(static::class)
                    ->getOrchestrator()
                    ->getRepository()
                    ->findOneBy([
                        "updated_at" => $updated_at,
                    ])
                    ?->getId() ?? 0,
            )
            ->first();
    }

    /**
     * Obtém instância do orquestrador
     */
    abstract protected function getOrchestrator(): object;
}
