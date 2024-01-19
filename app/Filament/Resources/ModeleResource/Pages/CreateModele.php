<?php

namespace App\Filament\Resources\ModeleResource\Pages;

use Filament\Pages\Actions\Action;
use App\Filament\Resources\ModeleResource;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;

class CreateModele extends CreateRecord
{
    protected static ?string $title = 'Ajouter un modèle';

    protected static string $resource = ModeleResource::class;
    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::modeles_create()->value,
          
        ]);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::modeles_create()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
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
