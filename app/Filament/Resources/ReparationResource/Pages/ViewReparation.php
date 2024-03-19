<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use Actions\Action;
use App\Models\Role;
use App\Models\User;
use App\Models\Engine;
use App\Models\Circuit;
use App\Models\Reparation;
use Filament\Pages\Actions;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Hidden;
use Filament\Pages\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ReparationResource;
use App\Support\Database\ReparationValidationStates;
use Filament\Notifications\Actions\Action as NotificationActions;

class ViewReparation extends ViewRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasPermissionTo(PermissionsClass::Reparation_update()->value)) {
            return [

                EditAction::make('edit')
                    ->visible(fn($record) => ($record->validation_state == 'nextValue' || $record->validation_step == 100 || $record->validation_state == ReparationValidationStates::Rejete()->value) ? false : true),

                Actions\Action::make('Valider')
                    ->color('success')
                    ->visible(function ($record) {

                        if ($this->record->validation_state == ReparationValidationStates::Rejete()->value) {

                            return false;
                        } else {

                            $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                            foreach ($circuit as $key => $item) {

                                $roleIds[] = $item['role_id'];
                            }

                            $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                            $user = auth()->user();

                            if (array_key_exists($this->record->validation_step, $roleIds)) {    // ensure array key is not off limits
                                $indice = $roleIds[$this->record->validation_step];

                                $requiredRole = Role::where('id', $indice)->first();

                                if (
                                    in_array($requiredRole, [
                                        Role::where('name', RolesEnum::Chef_parc()->value)->first(),
                                        Role::where('name', RolesEnum::Directeur_general()->value)->first(),
                                        Role::where('name', RolesEnum::Diga()->value)->first(),
                                        Role::where('name', RolesEnum::Budget()->value)->first(),
                                        Role::where('name', RolesEnum::Dpl()->value)->first(),

                                    ]) && $user->hasRole(Role::where('id', $indice)->value('id'))
                                ) {   // if require role is in list (array) and user has the role
        
                                    return true;
                                } elseif ($user->hasRole(Role::where('id', $indice)->value('id')) && ($user->departement_id == intval($concernedEngine->departement_id))) {
                                    return true;
                                } else {
                                    return false;
                                }

                            } else {
                                return false;
                            }
                        }

                    })
                    ->icon('heroicon-o-check-circle')
                    ->after(function () {

                        $currentValidationStep = $this->record->validation_step;

                        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                        $circuit = Circuit::where('id', $this->data['circuit_id'])->first()->steps;

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        if (array_key_exists($currentValidationStep, $roleIds)) {
                            $NextdestinataireRole = Role::find($roleIds[$currentValidationStep])->name;

                            $destinataire = User::role($NextdestinataireRole)->first();


                            if ($destinataire) {

                                if ($NextdestinataireRole) {

                                    if (in_array($NextdestinataireRole, [RolesEnum::Directeur()->value, RolesEnum::Chef_division()->value]) && $destinataire->departement_id == $concernedEngine->departement_id) {

                                        $realDestination = User::role($NextdestinataireRole)->where("departement_id", $concernedEngine->departement_id)->first();

                                        Notification::make()
                                            ->title('Demande de validation')
                                            ->body('Réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . ' en attente de validation')
                                            ->actions([
                                                NotificationActions::make('voir')
                                                    ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                                                    ->button()
                                                    ->color('primary'),
                                            ])
                                            ->sendToDatabase($realDestination);

                                    } elseif (
                                        in_array($NextdestinataireRole, [
                                            RolesEnum::Directeur_general()->value,
                                            RolesEnum::Diga()->value,
                                            RolesEnum::Chef_parc()->value,
                                            RolesEnum::Budget()->value,
                                        ])
                                    ) {
                                        Notification::make()
                                            ->title('Nouvelle demande')
                                            ->body('Réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . ' en attente de validation')
                                            ->actions([
                                                NotificationActions::make('voir')
                                                    ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                                                    ->button()
                                                    ->color('primary'),
                                            ])
                                            ->sendToDatabase($destinataire);
                                    }
                                }

                            }
                        }

                    })
                    ->action(function (?Reparation $record) {

                        $user = auth()->user();

                        $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        if ($user->hasRole(Role::where('name', RolesEnum::Chef_parc()->value)->first()->name)) {

                            if (!$this->record->facture || !$this->record->ref_proforma || !$this->record->cout_reparation) {

                                Notification::make()
                                    ->title('Attention')
                                    ->warning()
                                    ->body('Les informations du devis doivent être renseignées avant validation')
                                    ->send();

                                $this->halt();
                            }

                        }

                        //from here  check to oblige budget to set bon de commande before validating
        
                        if ($this->record) {

                            $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
        
                            $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                            $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                            $arrayDivided = array_chunk($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role
        
                            $ArrayToUse = array_flip($arrayDivided[1]);  //flip array to get keys
        
                            if ($user->hasRole(Role::where('name', RolesEnum::Budget()->value)->first()->name) && in_array($this->record->validation_step, $ArrayToUse)) {

                                if (!$this->record->bon_commande) {

                                    Notification::make()
                                        ->title('Attention')
                                        ->warning()
                                        ->body('Le bon de commande doit être joint avant validation')
                                        ->send();

                                    $this->halt();
                                }

                            }
                        }

                        //to here  check to oblige budget to set bon de commande before validating
        
                        //from here check to see if date fin is set before validation
                        if ($this->record) {

                            $searchedRoleId = (Role::where('name', RolesEnum::Chef_parc()->value)->first())->id;

                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
        
                            $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                            $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                            $secondSlicedArray = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1);

                            $thirdOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $secondSlicedArray)) + $secondOccurenceOfRoleInOriginalRolesArray + 1;

                            $arrayDivided = array_chunk($roleIds, $thirdOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role
        
                            if ($user->hasRole(Role::where('name', RolesEnum::Chef_parc()->value)->first()->name) && ($this->record->validation_step == array_key_last($roleIds))) {

                                if (!$this->record->date_fin) {

                                    Notification::make()
                                        ->title('Attention')
                                        ->warning()
                                        ->body("La date de retour de l'engin doit être renseignée avant validation")
                                        ->send();

                                    $this->halt();
                                }

                            }
                        }

                        //to here check to see if date fin is set before validation
        
                        $currentKey = $this->record->validation_step; // key in array
        
                        $currentvalue = $roleIds[$this->record->validation_step]; // value of the key array
        
                        $a = 0;
                        for ($i = 0; $i < count($roleIds); $i++) {

                            if ($i == $currentKey) {

                                next($roleIds);
                                break;
                            }

                            $a = next($roleIds);

                        }

                        $nextKey = key($roleIds);

                        $nextValue = current($roleIds);

                        if ($nextKey != null) {
                            $this->record->update([
                                'validation_step' => $nextKey,
                                'validation_state' => $nextValue,
                            ]);
                        } else {
                            $this->record->update([
                                'validation_step' => 100, //insert a dummy value if  we reach last array  value
                                'validation_state' => 'nextValue',
                            ]);
                        }

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
                        MarkdownEditor::make('motif_rejet')
                            ->label('Motif du rejet')
                            ->disableAllToolbarButtons()
                            ->enableToolbarButtons([

                            ])
                            ->columnSpanFull()
                            ->placeholder('Donnez la raison du rejet'),

                        Hidden::make('rejete_par')
                            ->default(auth()->user()->id),
                    ])

                    ->visible(function ($record) {

                        if ($this->record->validation_state == ReparationValidationStates::Rejete()->value) {

                            return false;
                        } elseif ($this->record->validation_state != 'nextValue') { //if reparation is not finished
        
                            $user = auth()->user();

                            $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                            foreach ($circuit as $key => $item) {

                                $roleIds[] = $item['role_id'];
                            }

                            $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                            $requiredRole = Role::find($this->record->validation_state)->name;

                            if (
                                $user->hasAnyRole([
                                    RolesEnum::Chef_division()->value,
                                    RolesEnum::Chef_parc()->value,
                                    RolesEnum::Diga()->value,
                                    RolesEnum::Directeur()->value,
                                    RolesEnum::Directeur_general()->value,
                                    RolesEnum::Budget()->value,
                                    RolesEnum::Dpl()->value,
                                ]) && (in_array($requiredRole, $user->getRoleNames()->toArray())) // if required role is within  user's roles
                            ) {

                                if (
                                    in_array($requiredRole, [    //roles that don't require being of same department before seeing reject button
                                        RolesEnum::Chef_parc()->value,
                                        RolesEnum::Directeur_general()->value,
                                        RolesEnum::Diga()->value,
                                        RolesEnum::Budget()->value,
                                    ])
                                ) {
                                    return true;
                                } elseif (
                                    in_array($requiredRole, [
                                        RolesEnum::Directeur()->value,
                                        RolesEnum::Chef_division()->value,
                                    ]) && $user->departement_id == $concernedEngine->departement_id
                                ) {
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }

                        } else {
                            return false;
                        }

                    })
                    ->action(function ($record, $data) {

                        $this->record->update(['validation_state' => ReparationValidationStates::Rejete()->value]);

                        $this->record->update(['motif_rejet' => $data['motif_rejet']]);

                        $this->record->update(['rejete_par' => $data['rejete_par']]);

                        Notification::make()
                            ->title('Rejeté(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // Actions\Action::make('Valider (Chef parc Bon de travail)')
                //     ->color('success')
                //     ->icon('heroicon-o-check-circle')
                //     ->action(function (?Reparation $record) {

                //         $this->record->update(['validation_state' => ReparationValidationStates::Bon_de_travail_chef_parc()->value]);
                //         Notification::make()
                //             ->title('Validé(e)')
                //             ->success()
                //             ->persistent()
                //             ->send();
                //     })
                //     ->visible(function ($record) {

                //         $user = auth()->user();

                //         $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

                //         if ($user->hasRole(RolesEnum::Chef_parc()->value) && (($this->record->cout_reparation)) && (($this->record->facture)) && (($this->record->ref_proforma) && ($this->record->validation_state == ReparationValidationStates::Demande_de_travail_diga()->value))) {
                //             return true;
                //         } else
                //             return false;
                //     }),
            ];
        }

        return [];
    }
}
