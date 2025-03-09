<?php

namespace LaravelInsight\Resources\DiscoveredModelResource\Pages;

use Filament\Resources\Pages\ListRecords;
use LaravelInsight\Resources\DiscoveredModelResource;

class ListDiscoveredModels extends ListRecords
{
    protected static string $resource = DiscoveredModelResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
