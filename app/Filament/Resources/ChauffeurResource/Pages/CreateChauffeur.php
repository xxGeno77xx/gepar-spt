<?php

namespace App\Filament\Resources\ChauffeurResource\Pages;

use App\Models\Engine;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ChauffeurResource;

class CreateChauffeur extends CreateRecord
{
    protected static string $resource = ChauffeurResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Chauffeurs_create()->value]);

        abort_if(!$userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter '))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }
}
