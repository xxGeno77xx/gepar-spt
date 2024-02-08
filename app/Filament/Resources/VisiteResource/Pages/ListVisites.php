<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVisites extends ListRecords
{
    protected static string $resource = VisiteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter une visite'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->leftjoin('users', 'visites.user_id', '=', 'users.id')
            ->join('engines', 'visites.engine_id', 'engines.id')
            ->select('engines.plate_number', 'visites.*', 'users.name')
            ->where('visites.state', StatesClass::Activated()->value);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::visites_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
