<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use Filament\Pages\Actions\Action;
use App\Support\Database\RolesEnum;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Database\Seeders\RolesPermissionsSeeder;
use App\Filament\Resources\DepartementResource;

class CreateDepartement extends CreateRecord
{
    protected static ?string $title = 'Ajouter un département';

    protected static string $resource = DepartementResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_create()->value]);

        $userRole = $user->hasRole([RolesEnum::Super_administrateur()->value]);

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter '))
            ->submit('create')
            ->keyBindings(['mod+s']);
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
}
