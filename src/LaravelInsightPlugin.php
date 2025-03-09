<?php

namespace LaravelInsight;

use Filament\Contracts\Plugin;
use Filament\Panel;
use LaravelInsight\Models\DiscoveredModel;

class LaravelInsightPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-insight';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            DiscoveredModel::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }
}
