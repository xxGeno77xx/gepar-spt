<?php

namespace App\Filament\Resources\MarqueResource\Pages;

use App\Filament\Resources\MarqueResource;
use App\Models\Marque;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarque extends EditRecord
{
    protected static string $resource = MarqueResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::marques_delete()->value])) {
            return [
                // Actions\DeleteAction::make(),
                Actions\Action::make('Supprimer')
                    ->color('danger')
                    ->icon("heroicon-o-eye-off")
                    ->action(function (?Marque $record) {
                        $this->record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/marques');
                        Notification::make()
                            ->title('Supprimé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->requiresConfirmation(),

            ];

        }

        return [];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::marques_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
