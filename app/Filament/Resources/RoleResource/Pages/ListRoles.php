<?php

namespace App\Filament\Resources\RoleResource\Pages;

use Filament\Pages\Actions;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\ListRecords;
use App\Support\Database\PermissionsClass;

class ListRoles extends ListRecords
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.RoleResource');
    }

    protected function getActions(): array
    {
        if(auth()->user()->hasPermissionTo(PermissionsClass::Roles_create()->value))
        {
            return [
                Actions\CreateAction::make()->label('Ajouter un rôle'),
            ];

        }
        return[];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::Permissions_read()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
 
}
