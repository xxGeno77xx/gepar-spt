<?php

namespace App\Filament\Resources\ChauffeurResource\Pages;

use App\Filament\Resources\ChauffeurResource;
use App\Models\Chauffeur;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChauffeur extends EditRecord
{
    protected static string $resource = ChauffeurResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Chauffeurs_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::marques_delete()->value])) {
            return [

                // Actions\Action::make('Supprimer')
                //     ->color('danger')
                //     ->icon("heroicon-o-eye-off")
                //     ->action(function (?Chauffeur $record) {
                //         $this->record->update(['state' => StatesClass::Deactivated()->value]);
                //         redirect('/chauffeurs');
                //         Notification::make()
                //             ->title('Supprimé(e)')
                //             ->success()
                //             ->persistent()
                //             ->send();
                //     })
                //     ->requiresConfirmation(),

            ];

        }

        return [];
    }
}
