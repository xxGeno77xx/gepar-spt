<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Config;

class ViewPermission extends ViewRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.PermissionResource');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Permissions_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
