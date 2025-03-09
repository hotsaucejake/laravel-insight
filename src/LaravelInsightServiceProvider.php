<?php

namespace LaravelInsight;

use Illuminate\Support\ServiceProvider;

class LaravelInsightServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-insight.php' => config_path('laravel-insight.php'),
        ], 'laravel-insight-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-insight.php',
            'laravel-insight'
        );
    }

    public function register(): void {}
}
