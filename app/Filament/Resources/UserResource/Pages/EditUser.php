<?php

namespace App\Filament\Resources\UserResource\Pages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\EditRecord;
use App\Support\Database\PermissionsClass;
use Phpsa\FilamentAuthentication\Events\UserUpdated;

class EditUser extends EditRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Event::dispatch(new UserUpdated($this->record));
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::Users_update()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
