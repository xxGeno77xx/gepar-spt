<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Filament\Resources\ReparationResource;
use App\Models\DepartementUser;
use App\Support\Database\PermissionsClass;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReparations extends ListRecords
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle réparation'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {
        $loggedUser = auth()->user();

        if (
            $loggedUser->hasAnyRole([
                RolesEnum::Chef_parc()->value,
                RolesEnum::Dpl()->value,
                RolesEnum::Budget()->value,
                RolesEnum::Diga()->value,
                RolesEnum::Directeur_general()->value,
                RolesEnum::Interimaire_DG()->value,

            ]) || $loggedUser->hasRole(RolesEnum::Super_administrateur()->value)
        ) {
            return static::getResource()::getEloquentQuery()
                ->join('engines', 'reparations.engine_id', 'engines.id')
                ->leftjoin('fournisseur', 'fournisseur.code_fr', 'reparations.prestataire_id')
                ->select('engines.plate_number', 'reparations.*')
                ->whereNot('reparations.state', StatesClass::Deactivated()->value);
        } elseif (
            $loggedUser->hasAnyRole([
                RolesEnum::Directeur()->value,
                RolesEnum::Delegue_Direction()->value,
                RolesEnum::Directeur_general()->value,
                RolesEnum::Delegue_Direction_Generale()->value,
                RolesEnum::Delegue_Division()->value,
                RolesEnum::Chef_division()->value,
            ])
        ) {

            $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->pluck('departement_code_centre')->toArray();

            return static::getResource()::getEloquentQuery()
                ->join('engines', 'reparations.engine_id', 'engines.id')
                ->leftjoin('fournisseur', 'fournisseur.code_fr', 'reparations.prestataire_id')
                ->whereIn('engines.departement_id', $userCentresCollection)
                ->select('engines.plate_number', 'reparations.*')
                ->where('reparations.state', StatesClass::Activated()->value);
        }

        return static::getResource()::getEloquentQuery()::query()->whereRaw('1 = 0');

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getTableFiltersFormColumns(): int
    {
        return 1;
    }
}
