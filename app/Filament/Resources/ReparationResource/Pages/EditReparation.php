<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Models\Engine;
use App\Models\Reparation;
use Filament\Forms\Components\Hidden;
use Filament\Pages\Actions;
use Forms\Components\Textarea;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\RichEditor;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ReparationResource;
use App\Support\Database\ReparationValidationStates;

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
                    ->icon('heroicon-o-eye-off')
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

                    Actions\Action::make('Valider (Chef div)')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_Chef_division()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),



                    Actions\Action::make('Valider (Directeur)')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_directeur_division()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),


                    Actions\Action::make('Valider (Chef parc)')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_chef_parc()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),


                    Actions\Action::make('Valider'.strtoupper('diga'))
                    ->label('DIGA')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_diga()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                    Actions\Action::make('Rejeter')
                    ->label('Rejeter')
                    ->color('danger')
                    ->icon('heroicon-o-x')
                    ->form([
                        RichEditor::make('motif_rejet')
                        ->label('Motif du rejet')
                        ->required(),

                        Hidden::make('rejete_par')
                        ->default(auth()->user()->id)
                    ])
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Rejete()->value]);
                        Notification::make()
                            ->title('Rejeté(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })

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

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

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
