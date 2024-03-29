<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use App\Models\Departement;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartement extends EditRecord
{
    protected static string $resource = DepartementResource::class;

    protected function getActions(): array
    {
        // if (auth()->user()->hasAnyPermission([PermissionsClass::departements_delete()->value])) {
        //     return [
        //         // Actions\DeleteAction::make(),
        //         Actions\Action::make('Supprimer')
        //             ->color('danger')
        //             ->action(function (?Departement $record) {
        //                 $this->record->update(['state' => StatesClass::Deactivated()->value]);
        //                 redirect('/departements');
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

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        // $userPermission = $user->hasAnyPermission([PermissionsClass::departements_update()->value]);

        $userRole = $user->hasRole([RolesPermissionsSeeder::SuperAdmin]);

        abort_if(! $userRole, 403, __("Vous n'avez pas access à cette page"));

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
