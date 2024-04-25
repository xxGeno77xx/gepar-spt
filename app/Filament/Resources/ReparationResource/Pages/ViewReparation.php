<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use Actions\Action;
use App\Filament\Resources\ReparationResource;
use App\Models\Circuit;
use App\Models\DepartementUser;
use App\Models\Engine;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use App\Support\Database\CircuitsEnums;
use App\Support\Database\PermissionsClass;
use App\Support\Database\ReparationValidationStates;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Actions\Action as NotificationActions;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReparation extends ViewRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasPermissionTo(PermissionsClass::Reparation_update()->value)) {
            return [

                EditAction::make('edit')
                    ->visible(fn ($record) => ($record->validation_state == 'nextValue' || $record->validation_step == 100 || $record->validation_state == ReparationValidationStates::Rejete()->value) ? false : true),

                Actions\Action::make('tranfert')
                    ->label('Transférer vers la DIGA')
                    ->icon('heroicon-o-arrow-circle-right')
                    ->requiresConfirmation()
                    ->visible(function () {

                        if ($this->record) {
                            if (

                                //if in  any diga circuit
                                in_array($this->record->circuit_id, [
                                    Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dir()->value)->first()->id,
                                    Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dg()->value)->first()->id,
                                    Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dir()->value)->first()->id,
                                    Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dg()->value)->first()->id,
                                    Circuit::where('name', CircuitsEnums::circuit_de_la_direction_generale_diga()->value)->first()->id,
                                    Circuit::where('name', CircuitsEnums::circuit_particulier_diga()->value)->first()->id,
                                ])
                            ) {
                                return false;
                            } elseif (in_array($this->record->validation_state, [ReparationValidationStates::Rejete()->value, ReparationValidationStates::Termine()->value])) {

                                return false;

                            } else {
                                $user = auth()->user();

                                $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                if (array_key_exists($this->record->validation_step, $roleIds)) {

                                    $searchedRoleId = (Role::where('name', RolesEnum::Directeur()->value)->first())->id;

                                    $directeurGeneralId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                                    $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                    $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                                    $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1; //second array key where role occurs

                                    $firstOccurenceOfDirecteurGeneral = array_search($directeurGeneralId, $roleIds); // first array key where role occurs

                                    $sliceForSecondDgRole = array_slice($roleIds, $firstOccurenceOfDirecteurGeneral + 1);

                                    $secondOccurenceOfDgInOriginalRolesArray = (array_search($directeurGeneralId, $sliceForSecondDgRole)) + $firstOccurenceOfDirecteurGeneral + 1;

                                    if (in_array($this->record->validation_step, [$secondOccurenceOfRoleInOriginalRolesArray, $secondOccurenceOfDgInOriginalRolesArray])) {

                                        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                                        $indice = $roleIds[$this->record->validation_step];

                                        $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

                                        foreach ($userCentresCollection as $userCentre) {
                                            $userCentresIds[] = $userCentre->departement_code_centre;
                                        }

                                        if ($user->hasAnyRole([RolesEnum::Directeur_general()->value,  RolesEnum::Interimaire_DG()->value]) ) {
                                            return true;

                                        } elseif (($user->hasRole(Role::where('id', $indice)->value('id')) ||  $user->hasRole(RolesEnum::Interimaire_Directeur()->value)) && (in_array(intval($concernedEngine->departement_id), $userCentresIds))) {

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

                            }
                        }
                    })
                    ->action(function () {

                        $user = auth()->user();

                        if ($user->hasRole(RolesEnum::Directeur()->value)) {

                            if ($this->record->circuit_id == Circuit::where('name', CircuitsEnums::circuit_de_direction()->value)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dir()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dir()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            } elseif ($this->record->circuit_id == Circuit::where('name', Circuit::where('name', CircuitsEnums::circuit_de_division()->value)->first()->name)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dir()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dir()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            }

                        } elseif ($user->hasRole(RolesEnum::Directeur_general()->value)) {

                            if ($this->record->circuit_id == Circuit::where('name', CircuitsEnums::circuit_de_direction()->value)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dg()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_de_direction_diga_dg()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            } elseif ($this->record->circuit_id == Circuit::where('name', CircuitsEnums::circuit_de_division()->value)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dg()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_de_division_diga_dg()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            } elseif ($this->record->circuit_id == Circuit::where('name', CircuitsEnums::circuit_de_la_direction_generale()->value)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_de_la_direction_generale_diga()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_de_la_direction_generale_diga()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            } elseif ($this->record->circuit_id == Circuit::where('name', CircuitsEnums::circuit_particulier()->value)->first()->id) {

                                //get diga version of the circuit
                                $circuit = Circuit::where('name', CircuitsEnums::circuit_particulier_diga()->value)->value('steps');

                                $circuitID = Circuit::where('name', CircuitsEnums::circuit_particulier_diga()->value)->first()->id;

                                $digaRoleID = Role::where('name', RolesEnum::Diga()->value)->first()->id;

                                foreach ($circuit as $key => $item) {

                                    $roleIds[] = $item['role_id'];
                                }

                                //find key in roleIds where role is  diga role
                                $digaKey = array_search($digaRoleID, $roleIds);

                                $this->record->update([
                                    'circuit_id' => $circuitID,
                                    'validation_step' => $digaKey,
                                    'validation_state' => $digaRoleID,

                                ]);
                            }
                        }

                        Notification::make('alerte')
                            ->title('Transmission de demande')
                            ->icon('heroicon-o-information-circle')
                            ->iconColor('danger')
                            ->body('Votre demande a été transmise à la DIGA')
                            ->send();

                    })
                // ->visible(function ($record) {

                //     if(!in_array($this->record->circuit_id, [/*get circuit IDS for circuits with diga in them*/ ]))  //  create circuits in seeder first
                //     {
                //        return false;
                //     }
                //     elseif ($this->record->validation_state == ReparationValidationStates::Rejete()->value) {

                //         return false;

                //     } else {

                //         $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                //         foreach ($circuit as $key => $item) {

                //             $roleIds[] = $item['role_id'];
                //         }

                //         $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                //         $user = auth()->user();

                //         if (array_key_exists($this->record->validation_step, $roleIds)) {    // ensure array key is not off limits

                //             $indice = $roleIds[$this->record->validation_step];

                //             $requiredRole = Role::where('id', $indice)->first();

                //             $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

                //             foreach ($userCentresCollection as $userCentre) {
                //                 $userCentresIds[] = $userCentre->departement_code_centre;
                //             }

                //             if (
                //                 in_array($requiredRole, [

                //                    RolesEnum::Directeur_general()->value,
                //                    RolesEnum::Directeur()->value,

                //                 ]) && $user->hasRole(Role::where('id', $indice)->value('id'))
                //             ) {   // if require role is in list (array) and user has the role

                //                 return true;
                //             } elseif ($user->hasRole(Role::where('id', $indice)->value('id')) && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) ) { // add third condition here :  must be in remaining steps following proforma step
                //                 return true;
                //             } else {
                //                 return false;
                //             }

                //         } else {
                //             return false;
                //         }
                //     }

                // })

                ,

                Actions\Action::make('Valider')
                    ->color('success')
                    ->requiresConfirmation()
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

                                $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

                                foreach ($userCentresCollection as $userCentre) {
                                    $userCentresIds[] = $userCentre->departement_code_centre;
                                }


                                if (
                                    in_array($requiredRole, [
                                        Role::where('name', RolesEnum::Chef_parc()->value)->first(),
                                        Role::where('name', RolesEnum::Directeur_general()->value)->first(),
                                        Role::where('name', RolesEnum::Diga()->value)->first(),
                                        Role::where('name', RolesEnum::Budget()->value)->first(),
                                        Role::where('name', RolesEnum::Dpl()->value)->first(),

                                    ])
                                ) {   // if require role is in list (array) and user has the role
                                     
                                    if($requiredRole == Role::where('name', RolesEnum::Directeur_general()->value)->first() )
                                    {
                                      
                                        if (($user->hasRole(RolesEnum::Directeur_general()->value)) || ($user->hasRole(RolesEnum::Interimaire_DG()->value))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Directeur()->value)->first())
                                    {
                                      
                                        if ($user->hasRole(RolesEnum::Directeur()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Directeur()->value ))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Chef_division()->value)->first() )
                                    {
                                    
                                        if ($user->hasRole(RolesEnum::Chef_division()->value   && (in_array(intval($concernedEngine->departement_id), $userCentresIds))|| $user->hasRole(RolesEnum::Interimaire_Chef_division()->value))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Chef_parc()->value)->first() )
                                    {
                                    
                                        if (($user->hasRole(RolesEnum::Chef_parc()->value) || ($user->hasRole(RolesEnum::Interimaire_Chef_parc()->value)))){
                                            return true;
                                        }

                                    } 
 
                                   elseif($user->hasRole(Role::where('id', $indice)->value('id')))   
                                   {
                                    return true;
                                   }
                                   

                                  
                                } elseif ($user->hasRole(Role::where('id', $indice)->value('id')) && (in_array(intval($concernedEngine->departement_id), $userCentresIds))) {
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

                        // $currentValidationStep = $this->record->validation_step;

                        // $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

                        // $circuit = Circuit::where('id', $this->data['circuit_id'])->first()->steps;

                        // foreach ($circuit as $key => $item) {

                        //     $roleIds[] = $item['role_id'];
                        // }

                        // if (array_key_exists($currentValidationStep, $roleIds)) {
                        //     $NextdestinataireRole = Role::find($roleIds[$currentValidationStep])->name;

                        //     $destinataire = User::role($NextdestinataireRole)->first();

                        //     if ($destinataire) {

                        //         if ($NextdestinataireRole) {

                        //             if (in_array($NextdestinataireRole, [RolesEnum::Directeur()->value, RolesEnum::Chef_division()->value]) && $destinataire->departement_id == $concernedEngine->departement_id) {

                        //                 $realDestination = User::role($NextdestinataireRole)->where('departement_id', $concernedEngine->departement_id)->first();

                        //                 Notification::make()
                        //                     ->title('Demande de validation')
                        //                     ->body('Réparation pour l\'engin immatriculé '.$concernedEngine->plate_number.' en attente de validation')
                        //                     ->actions([
                        //                         NotificationActions::make('voir')
                        //                             ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                        //                             ->button()
                        //                             ->color('primary'),
                        //                     ])
                        //                     ->sendToDatabase($realDestination);

                        //             } elseif (
                        //                 in_array($NextdestinataireRole, [
                        //                     RolesEnum::Directeur_general()->value,
                        //                     RolesEnum::Diga()->value,
                        //                     RolesEnum::Chef_parc()->value,
                        //                     RolesEnum::Budget()->value,
                        //                 ])
                        //             ) {
                        //                 Notification::make()
                        //                     ->title('Nouvelle demande')
                        //                     ->body('Réparation pour l\'engin immatriculé '.$concernedEngine->plate_number.' en attente de validation')
                        //                     ->actions([
                        //                         NotificationActions::make('voir')
                        //                             ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                        //                             ->button()
                        //                             ->color('primary'),
                        //                     ])
                        //                     ->sendToDatabase($destinataire);
                        //             }
                        //         }

                        //     }
                        // }

                    })
                    ->action(function (?Reparation $record) {

                        $user = auth()->user();

                        $circuit = Circuit::where('id', $this->record->circuit_id)->value('steps');

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        if ($user->hasRole(Role::where('name', RolesEnum::Chef_parc()->value)->first()->name)) {

                            if (! $this->record->facture || ! $this->record->ref_proforma || ! $this->record->cout_reparation) {

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

                                if (! $this->record->bon_commande) {

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

                                if (! $this->record->date_fin) {

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

                                $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

                                foreach ($userCentresCollection as $userCentre) {
                                    $userCentresIds[] = $userCentre->departement_code_centre;
                                }


                                if (
                                    in_array($requiredRole, [
                                        Role::where('name', RolesEnum::Chef_parc()->value)->first(),
                                        Role::where('name', RolesEnum::Directeur_general()->value)->first(),
                                        Role::where('name', RolesEnum::Diga()->value)->first(),
                                        Role::where('name', RolesEnum::Budget()->value)->first(),
                                        Role::where('name', RolesEnum::Dpl()->value)->first(),

                                    ])
                                ) {   // if require role is in list (array) and user has the role
                                     
                                    if($requiredRole == Role::where('name', RolesEnum::Directeur_general()->value)->first() )
                                    {
                                      
                                        if (($user->hasRole(RolesEnum::Directeur_general()->value)) || ($user->hasRole(RolesEnum::Interimaire_DG()->value))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Directeur()->value)->first())
                                    {
                                      
                                        if ($user->hasRole(RolesEnum::Directeur()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Directeur()->value ))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Chef_division()->value)->first() )
                                    {
                                    
                                        if ($user->hasRole(RolesEnum::Chef_division()->value   && (in_array(intval($concernedEngine->departement_id), $userCentresIds))|| $user->hasRole(RolesEnum::Interimaire_Chef_division()->value))){
                                            return true;
                                        }

                                    } 

                                    elseif($requiredRole == Role::where('name', RolesEnum::Chef_parc()->value)->first() )
                                    {
                                    
                                        if (($user->hasRole(RolesEnum::Chef_parc()->value) || ($user->hasRole(RolesEnum::Interimaire_Chef_parc()->value)))){
                                            return true;
                                        }

                                    } 
 
                                   elseif($user->hasRole(Role::where('id', $indice)->value('id')))   
                                   {
                                    return true;
                                   }
                                   

                                  
                                } elseif ($user->hasRole(Role::where('id', $indice)->value('id')) && (in_array(intval($concernedEngine->departement_id), $userCentresIds))) {
                                    return true;
                                } else {
                                    return false;
                                }

                            } else {
                                return false;
                            }
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
