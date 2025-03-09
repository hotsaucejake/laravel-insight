<?php

namespace LaravelInsight\Resources;

use Filament\Forms\Form;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaravelInsight\Models\DiscoveredModel;
use LaravelInsight\Resources\DiscoveredModelResource\Pages\ListDiscoveredModels;
use LaravelInsight\Resources\DiscoveredModelResource\Pages\ViewDiscoveredModel;

class DiscoveredModelResource extends Resource
{
    protected static ?string $model = DiscoveredModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Laravel Insights';

    public static function getEloquentQuery(): Builder
    {
        return DiscoveredModel::query();
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class')
                    ->label('Model Class')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscoveredModels::route('/'),
            'view' => ViewDiscoveredModel::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('tabs')
                ->schema([
                    Tab::make('General')
                        ->schema([
                            TextEntry::make('class'),
                        ]),
                ]),
        ]);
    }
}
