<?php

namespace App\Filament\Resources\TypeResource\Pages;

use App\Filament\Resources\TypeResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\RolesEnum;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateType extends CreateRecord
{
    protected static ?string $title = 'Ajouter un type';

    protected static string $resource = TypeResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_create()->value]);

        $userRole = $user->hasAnyRole([RolesPermissionsSeeder::SuperAdmin],
            RolesEnum::Chef_parc()->value,
            RolesEnum::Dpl()->value);

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
