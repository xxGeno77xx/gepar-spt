<?php

namespace App\Filament\Resources\TypeResource\Pages;

use Filament\Pages\Actions;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use App\Filament\Resources\TypeResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Database\Seeders\RolesPermissionsSeeder;

class ListTypes extends ListRecords
{
    protected static string $resource = TypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un type d\'engin'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_create()->value]);

        $userRole = $user->hasAnyRole([RolesPermissionsSeeder::SuperAdmin],
        RolesEnum::Chef_parc()->value,
        RolesEnum::Dpl()->value );

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->where('types_engins.state', StatesClass::Activated()->value);
    }
}
