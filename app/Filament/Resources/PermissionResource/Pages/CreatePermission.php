<?php

namespace App\Filament\Resources\PermissionResource\Pages;


use Illuminate\Support\Facades\Config;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

class CreatePermission extends CreateRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.PermissionResource');
    }

    public function afterSave(): void
    {
        if (! $this->record instanceof Permission) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }


}
