<?php

namespace LaravelInsight\Resources\DiscoveredModelResource\Pages;

use App\Filament\Admin\Resources\DiscoveredModelResource;
use Filament\Resources\Pages\ListRecords;

class ListDiscoveredModels extends ListRecords
{
    protected static string $resource = DiscoveredModelResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
