<?php

namespace App\Providers;

use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\User;
use App\Infrastructure\Boot\RequiredConfiguration;
use App\Infrastructure\Persistence\Crm\EloquentCrmRepository;
use App\Policies\CrmPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CrmRepository::class, EloquentCrmRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, CrmPolicy::class);

        if ($this->app->environment('local', 'testing')) {
            Model::preventLazyLoading(true);
        }

        $this->app->make(RequiredConfiguration::class)->assert();
    }
}
