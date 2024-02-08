<?php

namespace App\Filament\Resources\MarqueResource\Pages;

use App\Filament\Resources\MarqueResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMarques extends ListRecords
{
    protected static string $resource = MarqueResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter une marque'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::marques_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->where('marques.state', StatesClass::Activated()->value);
    }
}
