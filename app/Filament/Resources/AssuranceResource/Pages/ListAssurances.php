<?php

namespace App\Filament\Resources\AssuranceResource\Pages;

use App\Filament\Resources\AssuranceResource;
use App\Filament\Resources\AssuranceResource\Widgets\Assureurs;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAssurances extends ListRecords
{
    protected static string $resource = AssuranceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter une assurance'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join('engines', 'assurances.engine_id', '=', 'engines.id')
            ->select('engines.plate_number', 'assurances.*')
            ->where('assurances.state', StatesClass::Activated()->value);

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::assurances_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Assureurs::class,
        ];
    }
}
