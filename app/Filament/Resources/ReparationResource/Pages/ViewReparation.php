<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Filament\Resources\ReparationResource;
use App\Functions\ControlFunctions;
use App\Mail\ReparationMail;
use App\Models\Circuit;
use App\Models\DepartementUser;
use App\Models\Engine;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
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
use Illuminate\Support\Facades\Mail;

class ViewReparation extends ViewRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        
        if (auth()->user()->hasPermissionTo(PermissionsClass::Reparation_update()->value)) {
            return [

                EditAction::make('edit')
                    ->visible(function ($record) {


                        $NonEditors =[
                            RolesEnum::Chef_division()->value,
                            RolesEnum::Chef_section()->value,
                            RolesEnum::Directeur()->value,
                            RolesEnum::Directeur_general()->value,
                        ];


                        $loggedUser = auth()->user();


                        if($loggedUser->hasAnyRole( $NonEditors))
                        { 
                            if (intval($this->record->validation_step) == 0 && (intval($this->record->creator_id) == auth()->user()->id)) {
                                return true;
                            } 
                            return false;
                        }


                        if ($this->record->validation_state == ReparationValidationStates::Rejete()->value || $this->record->validation_state == 'nextValue') {

                            return false;

                        } elseif (intval($this->record->validation_step) == 0 && (intval($this->record->creator_id) == auth()->user()->id)) { dd('ok');
                            return true;
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

                                        Role::where('name', RolesEnum::Chef_dcgbt()->value)->first(),
                                        Role::where('name', RolesEnum::Chef_DPL()->value)->first(),

                                    ])
                                ) {   // if require role is in list (array) and user has the role
        
                                    if ($requiredRole == Role::where('name', RolesEnum::Directeur_general()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Directeur_general()->value)) || ($user->hasRole(RolesEnum::Interimaire_DG()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Directeur()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Directeur()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Directeur()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_division()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Chef_division()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Chef_division()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_parc()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Chef_parc()->value) || ($user->hasRole(RolesEnum::Interimaire_Chef_parc()->value)))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_DPL()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_DPL()->value))) {
                                            return true;
                                        }


                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_dcgbt()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_dcgbt()->value))) {
                                            return true;
                                        }

                                    } elseif ($user->hasRole(Role::where('id', $indice)->value('id'))) {
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

                    }),

                Actions\Action::make('Valider')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function ($record) {

                        if ($this->record->validation_state == ReparationValidationStates::Rejete()->value || $this->record->validation_state == 'nextValue') {

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

                                        Role::where('name', RolesEnum::Chef_dcgbt()->value)->first(),
                                        Role::where('name', RolesEnum::Chef_DPL()->value)->first(),
                                        Role::where('name', RolesEnum::Drhp()->value)->first(),

                                    ])
                                ) {   // if require role is in list (array) and user has the role
        
                                    if ($requiredRole == Role::where('name', RolesEnum::Directeur_general()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Directeur_general()->value)) || ($user->hasRole(RolesEnum::Interimaire_DG()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Directeur()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Directeur()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Directeur()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_division()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Chef_division()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Chef_division()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_parc()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Chef_parc()->value) || ($user->hasRole(RolesEnum::Interimaire_Chef_parc()->value)))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_DPL()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_DPL()->value))) {
                                            return true;
                                        }


                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_dcgbt()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_dcgbt()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Drhp()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Drhp()->value))) {
                                            return true;
                                        }

                                    } elseif ($user->hasRole(Role::where('id', $indice)->value('id'))) {
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

                                    if (in_array($NextdestinataireRole, [RolesEnum::Directeur()->value, RolesEnum::Chef_division()->value])) {


                                        $realDestination = User::role($NextdestinataireRole)->where('departement_id', $concernedEngine->departement_id)->get();

                                        $mailDestinator = User::role($NextdestinataireRole)->where('departement_id', $concernedEngine->departement_id)->where('notification', true)->get();

                                        if ($NextdestinataireRole == RolesEnum::Directeur()->value) {
                                            $ids = User::join("departement_user", "departement_user.user_id", "users.id")->where("departement_user.departement_code_centre", $concernedEngine->departement_id)->role(RolesEnum::Directeur()->value)->pluck("users.id");

                                            $realDestination = User::whereIn("id", $ids)->get();

                                            $mailDestinator = User::join("departement_user", "departement_user.user_id", "users.id")->where('notification', true)->where("departement_user.departement_code_centre", $concernedEngine->departement_id)->role(RolesEnum::Directeur()->value)->get();

                                        }



                                        if ($mailDestinator) {

                                            try {
                                                Mail::to($mailDestinator)->send(new ReparationMail($this->record));
                                            } catch (\Exception $e) {

                                                NotificationActions::make('voir')
                                                    ->body("Erreur lors de l'envoi du mail")
                                                    ->color('danger')
                                                    ->send()
                                                    ->close();
                                            }

                                        }

                                        if ($realDestination) {

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
                                        }

                                    } elseif (
                                        in_array($NextdestinataireRole, [
                                            RolesEnum::Directeur_general()->value,
                                            RolesEnum::Interimaire_DG()->value,
                                            RolesEnum::Diga()->value,
                                            RolesEnum::Chef_parc()->value,
                                            RolesEnum::Budget()->value,
                                            RolesEnum::Chef_dcgbt()->value,
                                            RolesEnum::Chef_DPL()->value,
                                            RolesEnum::Drhp()->value,
                                        ])
                                    ) {

                                        if ($NextdestinataireRole == RolesEnum::Directeur_general()->value) {
                                            $endpoint = User::Role([RolesEnum::Directeur_general()->value, RolesEnum::Interimaire_DG()->value])->get();

                                            Notification::make()
                                                ->title('Nouvelle demande')
                                                ->body('Réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . ' en attente de validation')
                                                ->actions([
                                                    NotificationActions::make('voir')
                                                        ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                                                        ->button()
                                                        ->color('primary'),

                                                ])
                                                ->sendToDatabase($endpoint);
                                        } else {

                                            $destinataire = User::role($NextdestinataireRole)->get();

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

                                        $mailDestinator = User::role($NextdestinataireRole)->where('notification', true)->pluck('email');

                                        if ($mailDestinator) {
                                            (Mail::to($mailDestinator)->send(new ReparationMail($this->record)));
                                        }

                                        $loggedUser = auth()->user();

                                        if ($loggedUser->hasAnyRole(RolesEnum::Directeur_general()->value)) {
                                            $this->record->update([
                                                "avis_dg" => "FAVORABLE",
                                                "date_valid_dg" => today(),
                                            ]);
                                        }

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

                            $budgetRoleKey = ControlFunctions::getNthOccurrenceOfRequiredRole($this->record, RolesEnum::Budget()->value, 2);

                            if ($user->hasRole(Role::where('name', RolesEnum::Budget()->value)->first()->name) && ($this->record->validation_step == $budgetRoleKey)) {

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

                        //from here check to see if date fin is set before validation
        
                        //check to oblige suivi budgetaire
        
                        if ($this->record) {

                            $budgetRoleKey = ControlFunctions::getNthOccurrenceOfRequiredRole($this->record, RolesEnum::Budget()->value, 1);

                            if ($user->hasRole(Role::where('name', RolesEnum::Budget()->value)->first()->name) && ($this->record->validation_step == $budgetRoleKey)) {

                                if (!$this->record->compte_imputation) {

                                    Notification::make()
                                        ->title('Attention')
                                        ->warning()
                                        ->body('Vous devez saisir les informations du suivi budgétaire pour continuer!!!')
                                        ->send();

                                    $this->halt();
                                }

                            }
                        }

                        // Diga avis check
                        // if ($this->record) {
        
                        //     $budgetRoleKey = ControlFunctions::getNthOccurrenceOfRequiredRole($this->record, RolesEnum::Diga()->value, 1);
        
                        //     if ($user->hasRole(Role::where('name', RolesEnum::Diga()->value)->first()->name) && ($this->record->validation_step == $budgetRoleKey)) {
        
                        //         if (! $this->record->avis_diga) {
        
                        //             Notification::make()
                        //                 ->title('Attention')
                        //                 ->warning()
                        //                 ->body('Donnez votre avis avant de poursuivre!!!')
                        //                 ->send();
        
                        //             $this->halt();
                        //         }
        
                        //     }
                        // }
                        //check to oblige suivi budgetaire
                        if ($this->record) {

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

                        if($this->record){

                            if(!is_null($this->record->date_valid_dg)){
 
                                return false;
                            }
                        }
                        

                        if ($this->record->validation_state == ReparationValidationStates::Rejete()->value || $this->record->validation_state == 'nextValue') {

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

                                        Role::where('name', RolesEnum::Chef_dcgbt()->value)->first(),
                                        Role::where('name', RolesEnum::Chef_DPL()->value)->first(),
                                        Role::where('name', RolesEnum::Drhp()->value)->first(),

                                    ])
                                ) {   // if require role is in list (array) and user has the role
        
                                    if ($requiredRole == Role::where('name', RolesEnum::Directeur_general()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Directeur_general()->value)) || ($user->hasRole(RolesEnum::Interimaire_DG()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Directeur()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Directeur()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Directeur()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_division()->value)->first()) {

                                        if ($user->hasRole(RolesEnum::Chef_division()->value && (in_array(intval($concernedEngine->departement_id), $userCentresIds)) || $user->hasRole(RolesEnum::Interimaire_Chef_division()->value))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_parc()->value)->first()) {

                                        if (($user->hasRole(RolesEnum::Chef_parc()->value) || ($user->hasRole(RolesEnum::Interimaire_Chef_parc()->value)))) {
                                            return true;
                                        }

                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_DPL()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_DPL()->value))) {
                                            return true;
                                        }


                                    } elseif ($requiredRole == Role::where('name', RolesEnum::Chef_dcgbt()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Chef_dcgbt()->value))) {
                                            return true;
                                        }

                                    } 
                                    
                                    elseif ($requiredRole == Role::where('name', RolesEnum::Drhp()->value)->first()) {
                                        if (($user->hasRole(RolesEnum::Drhp()->value))) {
                                            return true;
                                        }

                                    } 
                                    elseif ($user->hasRole(Role::where('id', $indice)->value('id'))) {
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

                        $loggedUser = auth()->user();

                        if ($loggedUser->hasAnyRole(RolesEnum::Directeur_general()->value)) {
                            $this->record->update([
                                "avis_dg" => "DEFAVORABLE",
                                "date_valid_dg" => today(),
                            ]);
                        }

                        Notification::make()
                            ->title('Rejeté(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Actions\Action::make('repairRecap')
                    ->label('Fiche de récap')
                    ->color('secondary')
                    ->icon('heroicon-o-cloud-download')
                    ->url(fn() => route('reparationRecap', $this->record))
                    ->visible(function () {

                        $loggedUser = auth()->user();

                        $reparation = $this->record;

                        if ((($reparation->avis_dg != null) && ($loggedUser->hasRole(RolesEnum::Budget()->value)))) {

                            return true;
                        }
                        return false;
                    })
                    ->openUrlInNewTab(),
            ];
        }

        return [];
    }
}
