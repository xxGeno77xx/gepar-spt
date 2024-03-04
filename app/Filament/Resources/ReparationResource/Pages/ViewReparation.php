<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use Actions\Action;
use App\Models\Engine;
use App\Models\Division;
use App\Models\Direction;
use App\Models\Reparation;
use Filament\Pages\Actions;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Pages\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\RichEditor;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ReparationResource;
use App\Support\Database\ReparationValidationStates;
use Filament\Pages\Actions\Action as FilamentPagesActions;

class ViewReparation extends ViewRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::Reparation_update()->value])) {
            return [

                EditAction::make("edit"),

                Actions\Action::make('Valider (Chef div)')
                    ->color('success')
                    ->visible(function ($record) {

                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        if (($user->hasRole(RolesEnum::Chef_division()->value) && ($user->departement_id == $concernedEngine->departement_id)  && ($this->record->validation_state == ReparationValidationStates::Declaration_initiale()->value))) {
                            return true;
                        } else
                            return false;
                    })
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        
                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_Chef_division()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();

                    }),

                Actions\Action::make('Valider (Directeur dept)')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {
                        
                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_directeur_division()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->visible( function ($record) {

                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        $division = Division::where("id", $user->departement_id)->first();

                        $direction = Direction::where("id", $division->direction_id)->first();
                        
                        if (($user->hasRole(RolesEnum::Directeur()->value) && ($division->direction_id ==  $direction->id) && ($this->record->validation_state == ReparationValidationStates::Demande_de_travail_Chef_division()->value))) {
                            return true;
                        } else
                            return false;
                    }),

                    Actions\Action::make('Valider (Directeur general)')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (?Reparation $record) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Demande_de_travail_dg()->value]);
                        Notification::make()
                            ->title('Validé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->visible(function ($record) {

                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        if ($user->hasRole(RolesEnum::Directeur_general()->value) && $this->record->validation_state == ReparationValidationStates::Demande_de_travail_directeur_division()->value) {
                            return true;
                        } else
                            return false;
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
                    })
                    ->visible(function ($record) {

                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        if ($user->hasRole(RolesEnum::Chef_parc()->value)) {
                            return true;
                        } else
                            return false;
                    }),


                Actions\Action::make('Valider' . strtoupper('diga'))
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
                    })
                    ->visible(function ($record) {

                        $user = auth()->user();

                        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                        if ($user->hasRole(RolesEnum::Diga()->value)) {
                            return true;
                        } else
                            return false;
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
                    ->visible(function ($record) {

                        $user = auth()->user();



                        if (
                            $user->hasAnyRole([
                                RolesEnum::Chef_division()->value,
                                RolesEnum::Chef_parc()->value,
                                RolesEnum::Diga()->value,
                                RolesEnum::Directeur()->value,
                                RolesEnum::Directeur_general()->value,
                                RolesEnum::Budget()->value,
                                RolesEnum::Dpl()->value,
                            ])
                        ) {
                            return true;
                        } else
                            return false;
                    }),
                 ];
        }

        return [];
    }
}
