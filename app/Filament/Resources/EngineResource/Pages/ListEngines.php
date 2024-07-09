<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use App\Models\Role;
use App\Support\Database\PermissionsClass;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEngines extends ListRecords
{
    protected static ?string $title = 'Engins';

    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un engin'),
        ];

    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {
        $loggedUser = auth()->user();

        $seeAll = [
            RolesEnum::Dpl()->value,
            RolesEnum::Chef_parc()->value,
            RolesPermissionsSeeder::SuperAdmin,
        ];

        $specific = Role::whereNotIn('name', $seeAll)->pluck('name')->toArray();

        if ($loggedUser->hasAnyRole($specific)) {
            return $this->specificQuery();
        } else {
            return $this->seeAllQuery();
        }
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

    public function specificQuery()
    {
        return $this->baseQuery()->where('engines.departement_id', auth()->user()->departement_id);
    }

    public function seeAllQuery()
    {
        return $this->baseQuery();
    }

    public function baseQuery()
    {
        return static::getResource()::getEloquentQuery()
            ->leftJoin('assurances', function ($join) {
                $join->on('engines.id', '=', 'assurances.engine_id')
                    ->where('assurances.state', StatesClass::Activated()->value)
                    ->whereRaw('assurances.id = (SELECT MAX(id) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [StatesClass::Activated()->value]);
            })
            ->leftJoin('visites', function ($join) {
                $join->on('engines.id', '=', 'visites.engine_id')
                    ->where('visites.state', StatesClass::Activated()->value)
                    ->whereRaw('visites.id = (SELECT MAX(id) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [StatesClass::Activated()->value]);
            })
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->select(
                'engines.id',
                'engines.plate_number',
                'marques.logo',
                'assurances.date_fin as date_fin',
                'visites.date_expiration as date_expiration',
                'engines.state',
                'engines.departement_id'
            );
    }
}
