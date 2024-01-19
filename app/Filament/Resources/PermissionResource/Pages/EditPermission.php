<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\EditRecord;
use App\Support\Database\PermissionsClass;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

class EditPermission extends EditRecord
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
