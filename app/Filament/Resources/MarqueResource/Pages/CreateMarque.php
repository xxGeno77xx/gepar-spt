<?php

namespace App\Filament\Resources\MarqueResource\Pages;


use App\Filament\Resources\MarqueResource;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;
class CreateMarque extends CreateRecord
{
    protected static ?string $title = 'Ajouter une marque';

    protected static string $resource = MarqueResource::class;
   

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::marques_create()->value]);
    
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
