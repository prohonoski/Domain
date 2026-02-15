<?php

namespace Proho\Domain\Traits\Filament;

use App\Models\EnsinoGestao\Instrutor;
use App\ORM\Entities\EnsinoGestao\InstrutorEntity;
use Filament\Notifications\Notification;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\Exceptions\BusinessRuleException;

/**
 * Trait HasOrchestratorActions
 *
 * Fornece métodos helper para executar ações de orquestradores com tratamento
 * automático de exceções de negócio e notificações no Filament.
 *
 * Uso:
 * 1. Use o trait na sua classe Resource ou Page
 * 2. Implemente o método abstrato getOrchestrator()
 * 3. Use os métodos executeCreateAction, executeUpdateAction ou executeDeleteAction
 *
 * Exemplo:
 * ```php
 * class MyResource extends Resource
 * {
 *     use HasOrchestratorActions;
 *
 *     protected function getOrchestrator(): MyOrchestrator
 *     {
 *         return app(MyOrchestrator::class);
 *     }
 *
 *     // Em uma action:
 *     $this->executeCreateAction(
 *         fn() => $this->getOrchestrator()->create($data),
 *         $action
 *     );
 * }
 * ```
 */
trait HasOrchestratorActions
{
    /**
     * Executa uma ação do orquestrador e trata exceções de negócio
     *
     * @param callable $action Função que executa a ação do orquestrador
     * @param string $successMessage Mensagem de sucesso a ser exibida
     * @param object|null $filamentAction Ação do Filament para halt em caso de erro
     * @return void
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
            throw $e; // Propaga o erro
            //$filamentAction?->halt();
        }
    }

    /**
     * Gera mensagem de sucesso baseada na ação e no modelo
     *
     * @param string $action Tipo de ação (create, update, delete)
     * @return string Mensagem formatada
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

    // deabilitar notificacao padrao
    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    // /**
    //  * Obtém o label do modelo (singular ou plural conforme contexto)
    //  *
    //  * Tenta obter o label através de:
    //  * 1. método getResource() se existir (para Pages)
    //  * 2. propriedade $pluralModelLabel se existir (para Resources)
    //  * 3. fallback para "Registro"
    //  *
    //  * @return string Label do modelo
    //  */
    // public function getModelLabel(): string
    // {
    //     if (method_exists($this, "getResource")) {
    //         return static::getResource()::getPluralModelLabel() ?? "Registro";
    //     }

    //     if (property_exists($this, "pluralModelLabel")) {
    //         return static::$pluralModelLabel ?? "Registro";
    //     }

    //     return "Registro";
    // }

    /**
     * Mensagem padrão para erros de regra de negócio
     *
     * @return string Mensagem de erro padrão
     */
    public function getBusinessErrorMessage(): string
    {
        return "Erro na validação";
    }

    /**
     * Executa ação de criação com tratamento de exceções
     *
     * @param callable $createCallback Callback que executa a criação
     * @param object|null $action Ação do Filament para halt em caso de erro
     * @return void
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
     *
     * @param callable $updateCallback Callback que executa a atualização
     * @param object|null $action Ação do Filament para halt em caso de erro
     * @return void
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
     *
     * @param callable $deleteCallback Callback que executa a exclusão
     * @param object|null $action Ação do Filament para halt em caso de erro
     * @return void
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
     *
     * @param callable $deleteBulkCallback Callback que executa as exclusões
     * @param object|null $action Ação do Filament para halt em caso de erro
     * @return void
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

    protected function getActionsList(): array
    {
        $actions = [];

        $actions = [
            0 => CreateAction::make()->action(function (
                array $data,
                CreateAction $action,
            ) {
                app(static::class)->executeCreateAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->create($data),
                    $action,
                );
                EntityManager::flush();
            }),
        ];

        return $actions;
    }

    protected function getActionsView(): array
    {
        $record = $this->record;

        $actions = [];
        if (isset($record) && $record instanceof Model) {
            $actions = [
                1 => $this->getDeleteAction(),
                2 => $this->getEditAction(),
            ];
        }
        return $actions;
    }

    protected function getActionsCreate(): array
    {
        $record = $this->record;

        $actions = [];
        if (isset($record) && $record instanceof Model) {
            $actions = [
                1 => $this->getDeleteAction(),
            ];
        }
        return $actions;
    }

    protected function getActionsEdit(): array
    {
        $record = $this->record;

        $actions = [];
        if (isset($record) && $record instanceof Model) {
            $actions = [
                1 => $this->getDeleteAction(),
            ];
        }
        return $actions;
    }

    protected function getEditAction(): ?EditAction
    {
        $record = $this->record;
        if (isset($record) && $record instanceof Model) {
            return EditAction::make()
                ->action(function (
                    Model $record,
                    array $data,
                    EditAction $action,
                ) {
                    app(static::class)->executeUpdateAction(
                        fn() => app(static::class)
                            ->getOrchestrator()
                            ->update($record->id, $data),
                        $this,
                    );
                    EntityManager::flush();
                    $this->redirect($this->getDeleteRedirectUrl());
                })
                ->successNotificationTitle(null);
        }
        return null;
    }
    protected function getDeleteAction(): ?DeleteAction
    {
        $record = $this->record;

        if (isset($record) && $record instanceof Model) {
            return DeleteAction::make()
                ->action(function (Model $record, DeleteAction $action) {
                    app(static::class)->executeDeleteAction(
                        fn() => app(static::class)
                            ->getOrchestrator()
                            ->delete($record->id),
                        $action,
                    );
                    EntityManager::flush();
                    $this->redirect($this->getDeleteRedirectUrl());
                })
                ->successNotificationTitle(null);
        }
        return null;
    }

    protected function getDeleteRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl("index");
    }

    protected function getActions(): array
    {
        $actions = parent::getActions();

        $csAction = [];

        if ($this instanceof CreateRecord) {
            $csAction = $this->getActionsCreate();
        } elseif ($this instanceof EditRecord) {
            $csAction = $this->getActionsEdit();
        } elseif ($this instanceof ListRecords) {
            $csAction = $this->getActionsList();

            // remover a acao criar default do filament
            foreach ($actions as $key => $item) {
                if ($item instanceof CreateAction) {
                    unset($actions[$key]);
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

        /*        return static::getResource()
            ::getModel()
            ::where(
                "id",
                app(static::class)
                    ->getOrchestrator()
                    ->repo->findOneBy([
                        "updated_at" => $updated_at,
                    ])
                    ?->getId() ?? 0,
            )
            ->first();
            */

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

    public static function getTableEditAction(): EditAction
    {
        return EditAction::make()
            ->action(function (Model $record, array $data, EditAction $action) {
                app(static::class)->executeUpdateAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->update($record->id, $data),
                    $action,
                );
                EntityManager::flush();
            })
            ->successNotificationTitle(null);
    }

    public static function getTableDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->action(function (Model $record, DeleteAction $action) {
                app(static::class)->executeDeleteAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->delete($record->id),
                    $action,
                );
                EntityManager::flush();
            })
            ->successNotificationTitle(null);
    }

    public static function getTableDeleteBulkAction(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->action(function ($records, DeleteBulkAction $action) {
                app(static::class)->executeDeleteBulkAction(function () use (
                    $records,
                ) {
                    foreach ($records as $record) {
                        app(static::class)
                            ->getOrchestrator()
                            ->delete($record->id);
                    }
                }, $action);
                EntityManager::flush();
            })
            ->successNotificationTitle(null);
    }

    public static function getTableCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->action(function (array $data, CreateAction $action) {
                app(static::class)->executeCreateAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->create($data),
                    $action,
                );
                EntityManager::flush();
            })
            ->successNotificationTitle(null);
    }

    /**
     * Obtém instância do orquestrador
     *
     * Este método deve ser implementado pela classe que usa o trait.
     * Geralmente retorna app(AlgumOrchestrator::class)
     *
     * @return object Instância do orquestrador
     */
    abstract protected function getOrchestrator(): object;
}
