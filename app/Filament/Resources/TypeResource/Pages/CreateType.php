<?php

namespace App\Filament\Resources\TypeResource\Pages;

use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use App\Filament\Resources\TypeResource;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Database\Seeders\RolesPermissionsSeeder;

class CreateType extends CreateRecord
{
    protected static ?string $title = 'Ajouter un type';

    protected static string $resource = TypeResource::class;
    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_create()->value]);
        
        $userRole = $user->hasRole([RolesPermissionsSeeder::SuperAdmin]);

    
        abort_if(!$userRole, 403, __("Vous n'avez pas access à cette page"));
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