<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\ViewRecord;
use App\Support\Database\PermissionsClass;

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
