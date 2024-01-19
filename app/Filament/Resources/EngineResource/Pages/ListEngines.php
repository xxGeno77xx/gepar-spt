<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EngineResource;
use App\Support\Database\PermissionsClass;

class ListEngines extends ListRecords
{
    protected static ?string $title = 'Engins';

    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un engin'),
        ];

    }

    protected function getTableRecordsPerPageSelectOptions(): array 
    {
        return [10, 25, 50, 100];
    } 

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
        ->leftJoin('assurances', function ($join) {
            $join->on('engines.id', '=', 'assurances.engine_id')
                ->where('assurances.state', 1)
                ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state = 1)');
        })
        ->leftJoin('visites', function ($join) {
            $join->on('engines.id', '=', 'visites.engine_id')
                ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = 1) ')
                ->where('visites.state', 1);
        })
        ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
        ->join('marques', 'modeles.marque_id', '=', 'marques.id')
        ->Join('departements','engines.departement_id','departements.id')
        // ->leftJoin('chauffeurs','engines.chauffeur_id','chauffeurs.id')
        // ->leftjoin('departements','chauffeurs.departement_id','departements.id')
        ->leftjoin('users','engines.user_id','users.id')
        ->where('engines.state',  '<>', StatesClass::Deactivated()->value)
        ->select('engines.*', 'departements.nom_departement', 'modeles.nom_modele', 'marques.nom_marque', 'marques.logo',
                     DB::raw('MAX(assurances.date_fin) as date_fin'),
                     DB::raw('MAX(visites.date_expiration) as date_expiration'), 'users.name', /*'chauffeurs.name as chauffeur'*/
        )
        ->groupBy('engines.id', 'departements.nom_departement', 'modeles.nom_modele', 'marques.nom_marque', 'marques.logo');

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_read()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
}
