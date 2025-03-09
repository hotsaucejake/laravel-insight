<?php

namespace LaravelInsight\Resources\DiscoveredModelResource\Pages;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Artisan;
use LaravelInsight\Resources\DiscoveredModelResource;

class ViewDiscoveredModel extends ViewRecord
{
    protected static string $resource = DiscoveredModelResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the model class from the discovered record.
        $modelClass = $this->record->class;

//        dd($data);

        // Run the model:show command with the --json flag.
        Artisan::call('model:show', [
            'model'  => $modelClass,
            '--json' => true,
        ]);
        $json = Artisan::output();

//        dd(json_decode($json));

        return array_merge($data, json_decode($json, true) ?? []);
    }
}
