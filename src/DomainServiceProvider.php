<?php

namespace Proho\Domain;

use Filament\FilamentServiceProvider;
use Spatie\LaravelPackageTools\Package;

class DomainServiceProvider extends FilamentServiceProvider
{
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
