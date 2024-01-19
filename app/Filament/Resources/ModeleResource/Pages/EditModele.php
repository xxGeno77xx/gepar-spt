<?php

namespace App\Filament\Resources\ModeleResource\Pages;

use App\Models\Modele;
use Filament\Pages\Actions;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ModeleResource;
use App\Support\Database\PermissionsClass;

class EditModele extends EditRecord
{
    protected static string $resource = ModeleResource::class;

    protected function getActions(): array
    {
        if(auth()->user()->hasAnyPermission([PermissionsClass::modeles_delete()->value]))
            {
                return [
                    // Actions\DeleteAction::make(),
                    Actions\Action::make('Supprimer')
                        ->color('danger')
                        ->action(function (?Modele $record) {
                            $this->record->update(['state' => StatesClass::Deactivated()->value]);
                            redirect('/modeles');
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
