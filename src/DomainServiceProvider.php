<?php

namespace Proho\Domain;

use Filament\FilamentServiceProvider;
use Proho\Domain\Traits\Filament\HasOrchestratorActionsV2;
use Proho\Domain\Traits\Filament\HasOrchestratorActionsV3;
use Spatie\LaravelPackageTools\Package;

class DomainServiceProvider extends FilamentServiceProvider
{
    public function register(): void
    {
        parent::register();

        // Filament v3 possui a classe Filament\Panel; v2 não.
        // Guard para evitar redeclaração em testes (register() pode ser chamado mais de uma vez)
        if (
            !trait_exists(
                "Proho\Domain\Traits\Filament\HasOrchestratorActions",
                false,
            )
        ) {
            $isV3 = class_exists(\Filament\Panel::class);

            if ($isV3) {
                class_alias(
                    HasOrchestratorActionsV3::class,
                    "Proho\Domain\Traits\Filament\HasOrchestratorActions",
                );
            } else {
                class_alias(
                    HasOrchestratorActionsV2::class,
                    "Proho\Domain\Traits\Filament\HasOrchestratorActions",
                );
            }
        }
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name("proho-domain")->hasConfigFile()->hasViews();
    }
}
