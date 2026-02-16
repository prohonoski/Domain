<?php

namespace Proho\Domain\Traits\Filament;

use Filament\Pages\Actions\CreateAction;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Illuminate\Database\Eloquent\Model;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Trait HasOrchestratorActionsV2
 *
 * Versão compatível com Filament v2.
 * Usa getActions() e namespaces Filament\Pages\Actions\*.
 *
 * Nota: getEditAction() e getDeleteAction() NÃO são sobrescritos aqui
 * porque as classes pai (ManageRecords, ViewRecord, EditRecord) definem
 * return types incompatíveis entre si. As actions são construídas inline
 * nos métodos getActionsView/Create/Edit.
 */
trait HasOrchestratorActionsV2
{
    use HasOrchestratorActionsBase;

    protected function getActions(): array
    {
        $actions = parent::getActions();
        return $this->buildPageActions($actions);
    }

    protected function getActionsList(): array
    {
        return [
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
    }

    protected function getActionsView(): array
    {
        $record = $this->record;

        $actions = [];
        if (isset($record) && $record instanceof Model) {
            $deleteAction = DeleteAction::make()->action(function (
                Model $record,
            ) {
                app(static::class)->executeDeleteAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->delete($record->id),
                );
                EntityManager::flush();
                $this->redirect($this->getDeleteRedirectUrl());
            });

            $editAction = EditAction::make()->action(function (
                Model $record,
                array $data,
            ) {
                app(static::class)->executeUpdateAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->update($record->id, $data),
                    $this,
                );
                EntityManager::flush();
                $this->redirect($this->getDeleteRedirectUrl());
            });

            $actions = [
                1 => $deleteAction,
                2 => $editAction,
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
                1 => DeleteAction::make()->action(function (Model $record) {
                    app(static::class)->executeDeleteAction(
                        fn() => app(static::class)
                            ->getOrchestrator()
                            ->delete($record->id),
                    );
                    EntityManager::flush();
                    $this->redirect($this->getDeleteRedirectUrl());
                }),
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
                1 => DeleteAction::make()->action(function (Model $record) {
                    app(static::class)->executeDeleteAction(
                        fn() => app(static::class)
                            ->getOrchestrator()
                            ->delete($record->id),
                    );
                    EntityManager::flush();
                    $this->redirect($this->getDeleteRedirectUrl());
                }),
            ];
        }
        return $actions;
    }

    public static function getTableEditAction(): TableEditAction
    {
        return TableEditAction::make()
            ->action(function (
                Model $record,
                array $data,
                TableEditAction $action,
            ) {
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

    public static function getTableDeleteAction(): TableDeleteAction
    {
        return TableDeleteAction::make()
            ->action(function (Model $record, TableDeleteAction $action) {
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
}
