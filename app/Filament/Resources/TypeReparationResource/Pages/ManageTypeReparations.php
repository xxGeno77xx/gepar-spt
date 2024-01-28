<?php

namespace App\Filament\Resources\TypeReparationResource\Pages;

use Filament\Pages\Actions;
use App\Support\Database\StatesClass;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\TypeReparationResource;

class ManageTypeReparations extends ManageRecords
{
    protected static string $resource = TypeReparationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un type de réparation'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()->where('state', StatesClass::Activated()->value);
    }
}
