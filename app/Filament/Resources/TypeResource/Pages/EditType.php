<?php

namespace App\Filament\Resources\TypeResource\Pages;

use App\Filament\Resources\TypeResource;
use App\Models\Type;
use App\Support\Database\PermissionsClass;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditType extends EditRecord
{
    protected static string $resource = TypeResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::types_delete()->value])) {
            return [
                // Actions\DeleteAction::make(),
                Actions\Action::make('Supprimer')
                    ->color('danger')
                    ->icon('heroicon-o-eye-off')
                    ->action(function (?Type $record) {
                        $this->record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/types');
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

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_create()->value]);

        $userRole = $user->hasAnyRole([RolesEnum::Super_administrateur()->value],
            RolesEnum::Chef_parc()->value,
            RolesEnum::Dpl()->value);

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
