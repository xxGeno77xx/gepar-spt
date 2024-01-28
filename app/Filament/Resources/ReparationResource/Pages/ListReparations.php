<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use Filament\Pages\Actions;
use Filament\Tables\Filters\Layout;
use App\Support\Database\StatesClass;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ReparationResource;

class ListReparations extends ListRecords
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter une réparation'),
        ];
    }
    protected function getTableRecordsPerPageSelectOptions(): array 
    {
        return [10, 25, 50, 100];
    } 

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join('engines','reparations.engine_id','engines.id')
            ->leftJoin('users','reparations.user_id','users.id')
            // ->join('prestataires', 'prestataires.id','reparations.prestataire_id')
            ->select('engines.plate_number','reparations.*','users.name',/*'prestataires.nom as prestataire'*/)
            ->where('reparations.state',StatesClass::Activated() );
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_read()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    // protected function getTableFiltersLayout(): ?string
    // {
    //     return Layout::AboveContent;
    // }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
    protected function getTableFiltersFormColumns(): int
    {
        return 1;
    }
 
}
