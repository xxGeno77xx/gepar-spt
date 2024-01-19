<?php

namespace App\Filament\Resources\RoleResource\Pages;

use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\EditAction;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\ViewRecord;
use App\Support\Database\PermissionsClass;

class ViewRole extends ViewRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.RoleResource');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::Roles_read()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getActions(): array
    {
        $resource = static::getResource();

        if (! $resource::hasPage('edit')) {
            return [];
        }

        if (! $resource::canEdit($this->getRecord())) {
            return [];
        }

        if (auth()->user()->hasPermissionTo(PermissionsClass::Roles_update()->value))
        {
            return [$this->getEditAction()];
        }
        
        
        return [];
    }
    
}

