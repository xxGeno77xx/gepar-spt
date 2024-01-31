<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Support\Database\PermissionsClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Config;

class ListUsers extends ListRecords
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function getActions(): array
    {
        if (auth()->user()->hasPermissionTo(PermissionsClass::Users_create()->value)) {
            return [
                Actions\CreateAction::make()->label('Ajouter un utilisateur'),
            ];

        }

        return [];

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Users_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
