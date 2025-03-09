<?php

namespace LaravelInsight;

use Filament\Panel;
use LaravelInsight\Resources\DiscoveredModelResource;

class LaravelInsightPlugin implements \Filament\Contracts\Plugin
{
    public function getId(): string
    {
        return 'laravel-insight';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            DiscoveredModelResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }
}
