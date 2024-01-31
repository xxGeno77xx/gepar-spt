<?php

namespace App\Filament\Resources\ModeleResource\Pages;

use App\Filament\Resources\ModeleResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListModeles extends ListRecords
{
    protected static string $resource = ModeleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un modèle'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join('marques', 'marques.id', '=', 'modeles.marque_id')
            ->select('marques.nom_marque', 'marques.logo', 'modeles.nom_modele', 'modeles.created_at', 'modeles.id')
            ->where('modeles.state', StatesClass::Activated());
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::modeles_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
