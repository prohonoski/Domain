<?php

namespace Proho\Domain\Traits\Filament;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Illuminate\Database\Eloquent\Model;
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Trait HasOrchestratorActionsV3
 *
 * Versão compatível com Filament v3.
 * Usa getHeaderActions() e namespaces Filament\Actions\*.
 */
trait HasOrchestratorActionsV3
{
    use HasOrchestratorActionsBase;

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();
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

    protected function getEditAction(): EditAction
    {
        $record = $this->record;
        $action = EditAction::make();

        if (isset($record) && $record instanceof Model) {
            $action->action(function (
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
            });
        }

        return $action;
    }

    protected function getDeleteAction(): DeleteAction
    {
        $record = $this->record;
        $action = DeleteAction::make();

        if (isset($record) && $record instanceof Model) {
            $action->action(function (Model $record, DeleteAction $action) {
                app(static::class)->executeDeleteAction(
                    fn() => app(static::class)
                        ->getOrchestrator()
                        ->delete($record->id),
                    $action,
                );
                EntityManager::flush();
                $this->redirect($this->getDeleteRedirectUrl());
            });
        }

        return $action;
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
