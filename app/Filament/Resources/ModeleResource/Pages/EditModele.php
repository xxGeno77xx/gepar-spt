<?php

namespace App\Filament\Resources\ModeleResource\Pages;

use App\Filament\Resources\ModeleResource;
use App\Models\Modele;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModele extends EditRecord
{
    protected static string $resource = ModeleResource::class;

    protected function getActions(): array
    {
        // if (auth()->user()->hasAnyPermission([PermissionsClass::modeles_delete()->value])) {
        //     return [
        //         // Actions\DeleteAction::make(),
        //         Actions\Action::make('Supprimer')
        //             ->color('danger')
        //             ->icon('heroicon-o-eye-off')
        //             ->action(function (?Modele $record) {
        //                 $this->record->update(['state' => StatesClass::Deactivated()->value]);
        //                 redirect('/modeles');
        //                 Notification::make()
        //                     ->title('Supprimé(e)')
        //                     ->success()
        //                     ->persistent()
        //                     ->send();
        //             })
        //             ->requiresConfirmation(),

        //     ];

        // }

        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::modeles_update()->value,

        ]);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::modeles_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
