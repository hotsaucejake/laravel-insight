<?php

namespace LaravelInsight\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use LaravelInsight\Models\DiscoveredModel;

class DiscoveredModelResource extends Resource
{
    protected static ?string $model = DiscoveredModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return DiscoveredModel::query();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
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
            'index' => Pages\ListDiscoveredModels::route('/'),
        ];
    }
}
