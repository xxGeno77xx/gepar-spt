<?php

namespace App\Filament\Resources\CircuitResource\Pages;

use App\Filament\Resources\CircuitResource;
use App\Models\Circuit;
use App\Support\Database\RolesEnum;
use Filament\Resources\Pages\CreateRecord;

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

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
