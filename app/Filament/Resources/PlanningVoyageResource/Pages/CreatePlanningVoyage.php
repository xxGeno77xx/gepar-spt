<?php

namespace App\Filament\Resources\PlanningVoyageResource\Pages;

use App\Filament\Resources\PlanningVoyageResource;
use App\Support\Database\RolesEnum;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanningVoyage extends CreateRecord
{
    protected static string $resource = PlanningVoyageResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userRole = $user->hasAnyRole([RolesEnum::Chef_parc()->value, RolesEnum::Super_administrateur()->value]);

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));
    }
}
