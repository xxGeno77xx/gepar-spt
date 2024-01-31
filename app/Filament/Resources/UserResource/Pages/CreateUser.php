<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Support\Database\PermissionsClass;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Phpsa\FilamentAuthentication\Events\UserCreated;

class CreateUser extends CreateRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function afterCreate(): void
    {
        Event::dispatch(new UserCreated($this->record));
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('filament::resources/pages/create-record.form.actions.create_another.label'))
            ->action('createAnother')
            ->label('Ajouter & ajouter un(e) autre')
            ->keyBindings(['mod+shift+s'])
            ->color('secondary');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Users_create()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
