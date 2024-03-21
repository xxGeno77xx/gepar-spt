<?php

namespace App\Filament\Resources\CircuitResource\Pages;

use App\Filament\Resources\CircuitResource;
use App\Support\Database\RolesEnum;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCircuit extends EditRecord
{
    protected static string $resource = CircuitResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasRole(RolesEnum::Super_administrateur()->value);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
