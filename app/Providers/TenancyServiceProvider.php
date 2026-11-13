<?php

namespace App\Providers;

use App\Contracts\TenantFileStorage;
use App\Support\Storage\TenantStorageFactory;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantDatabaseManager;
use App\Support\Tenancy\TenantFeatureSync;
use App\Support\Tenancy\TenantMigrator;
use App\Support\Tenancy\TenantResolver;
use App\Support\Tenancy\SupportEntryService;
use App\Support\Tenancy\ProvisionDatabaseManager;
use App\Support\Tenancy\TenantAiConfigApplier;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantDatabaseManager::class);
        $this->app->singleton(TenantFeatureSync::class);
        $this->app->singleton(TenantResolver::class);
        $this->app->singleton(TenantMigrator::class);
        $this->app->singleton(TenantAiConfigApplier::class);
        $this->app->singleton(ProvisionDatabaseManager::class);
        $this->app->singleton(SupportEntryService::class);
        $this->app->singleton(TenantStorageFactory::class);

        // Resolve per call so the current TenantContext is always used.
        $this->app->bind(TenantFileStorage::class, function ($app) {
            return $app->make(TenantStorageFactory::class)->forCurrent();
        });
    }

    public function boot(): void
    {
        //
    }
}
