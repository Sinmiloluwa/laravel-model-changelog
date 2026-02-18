<?php

namespace Sinmiloluwa\LaravelModelChangelog;

use Illuminate\Support\ServiceProvider;

class ModelChangelogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'model-changelog-migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/model-changelog.php',
            'model-changelog'
        );
    }
}