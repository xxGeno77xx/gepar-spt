<?php

namespace App\Filament\Resources\ChauffeurResource\Pages;

use App\Filament\Resources\ChauffeurResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListChauffeurs extends ListRecords
{
    protected static string $resource = ChauffeurResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Chauffeurs_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('Ajouter un chauffeur')),
        ];
    }

    // protected function getTableQuery(): Builder
    // {
    //     return static::getResource()::getEloquentQuery()
    //         // ->leftjoin('engines', 'engines.id', 'chauffeurs.engine_id')
    //         // ->leftjoin('departements', 'chauffeurs.departement_id', 'departements.id')
    //         ->select(/*'engines.plate_number', 'departements.nom_departement',*/ 'chauffeurs.*', 'departements.id as Did')
    //         ->where('chauffeurs.state', StatesClass::Activated()->value);
    // }
}
