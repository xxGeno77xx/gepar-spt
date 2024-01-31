<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.RoleResource');
    }

    public function afterSave(): void
    {
        if (! $this->record instanceof Role) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Roles_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
