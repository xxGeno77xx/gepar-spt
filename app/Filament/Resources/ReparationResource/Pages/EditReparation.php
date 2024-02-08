<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Filament\Resources\ReparationResource;
use App\Models\Engine;
use App\Models\Reparation;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReparation extends EditRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::reparation_delete()->value])) {
            return [
                // Actions\DeleteAction::make(),
                Actions\Action::make('Supprimer')
                    ->color('danger')
                    ->icon("heroicon-o-eye-off")
                    ->action(function (?Reparation $record) {
                        $this->record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/reparations');
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

        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        $reparation = $this->record;

        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        $reparation->update(['updated_at_user_id' => auth()->user()->id]);

        if ($reparation->date_fin) {
            $concernedEngine->update([
                'state' => StatesClass::Activated()->value,
            ]);
        }
    }
}
