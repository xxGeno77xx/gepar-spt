<?php

namespace App\Filament\Resources\CircuitResource\Pages;

use App\Models\Role;
use App\Models\Circuit;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Pages\Actions;
use App\Support\Database\RolesEnum;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CircuitResource;

class CreateCircuit extends CreateRecord
{
    protected static string $resource = CircuitResource::class;

    public function beforeCreate()
    {
        // $f = Circuit::first()->value("steps");
        
        // foreach ($f as $item) {
           
        //     $roleIds[] = $item['roles'];
        // }

        // dd($roleIds);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasRole(RolesEnum::Super_administrateur()->value);

        abort_if(!$userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
