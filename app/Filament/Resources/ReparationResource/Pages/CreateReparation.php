<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Models\Role;
use App\Models\User;
use App\Models\Engine;
use App\Models\Circuit;
use App\Models\Reparation;
use App\Models\Departement;
use App\Mail\ReparationMail;
use App\Models\DepartementUser;
use Filament\Pages\Actions\Action;
use App\Support\Database\RolesEnum;
use Illuminate\Support\Facades\Mail;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Select;
use App\Support\Database\CircuitsEnums;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Database\Seeders\RolesPermissionsSeeder;
use App\Filament\Resources\ReparationResource;
use App\Support\Database\ReparationValidationStates;
use Filament\Notifications\Actions\Action as NotificationActions;

class CreateReparation extends CreateRecord
{

    protected static ?string $title = 'Nouvelle maintenance';

    protected static string $resource = ReparationResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_create()->value]);

        abort_unless(($user->hasAnyRole([
            RolesPermissionsSeeder::SuperAdmin,
            RolesEnum::Chef_division()->value,
            RolesEnum::Chef_parc()->value,
            RolesEnum::Dpl()->value,
            RolesEnum::Delegue_Division()->value,
            RolesEnum::Directeur()->value,
            RolesEnum::Delegue_Direction()->value,
            RolesEnum::Directeur_general()->value,
            RolesEnum::Delegue_Direction_Generale()->value,
        ]) && $userPermission), 403, __("Vous n'avez pas access à cette page"));

    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter '))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function beforeCreate(): void
    {
        $newRaparation = $this->data;

        $latestReparation = Reparation::select(['engine_id', 'created_at', 'date_fin'])
            ->orderbydesc('created_at')
            ->where('engine_id', $newRaparation['engine_id'])
            ->where('state', StatesClass::Activated()->value)
            ->where('validation_state', '<>', ReparationValidationStates::Rejete()->value)
            ->first();

        if ($latestReparation) {
            if (!$latestReparation->date_fin) {
                Notification::make()
                    ->warning()
                    ->title('Attention!')
                    ->body('La réparation précédente n\'est pas encore terminée.Veuillez la compléter avant d\'en renseigner une nouvelle!!!')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        if (!is_null($newRaparation['date_fin'])) {

        }

    }

    public function afterCreate()
    {
        $this->sendNotificationToValidatior();

        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        if (is_null($this->record['date_fin'])) {
            $concernedEngine->update([
                'state' => StatesClass::Repairing()->value,
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {


        $engineCircuit = Engine::find($data["engine_id"])->circuit_id;
        

        $circuit = Circuit::find($engineCircuit)->first()->steps;

        foreach ($circuit as $key => $item) {

            $roleIds[] = $item['role_id'];
        }

        $result = $roleIds[0];

        $data['validation_state'] = $result;

        return $data;
    }

    public function sendNotificationToValidatior()
    {

        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        $circuit = Circuit::where('id', $this->record->circuit_id)->first()->steps;

        foreach ($circuit as $key => $item) {

            $roleIds[] = $item['role_id'];
        }

        $destinataireRole = Role::find($roleIds[0])->name;

        $destinataire = User::role($destinataireRole)->get(); //dd( $destinataire,  $destinataireRole);

        if ($destinataire) {



            if (in_array($destinataireRole, [RolesEnum::Directeur()->value, RolesEnum::Chef_division()->value])) {


                $realDestination = User::role($destinataireRole)->where('departement_id', $concernedEngine->departement_id)->first();

                Notification::make()
                    ->title('Nouvelle demande')
                    ->body('Demande de réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . '')
                    ->actions([
                        NotificationActions::make('voir')
                            ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                            ->button()
                            ->color('primary'),
                    ])
                    ->sendToDatabase($realDestination);

                try {
                    Mail::to($destinataire)->send(new ReparationMail($this->record));
                } catch (\Exception $e) {
                    Notification::make('erreur')
                        ->body("Erreur lors de l'envoi du mail au validateur. Veuillez le contacter pour l'informer")
                        ->send();
                }

            } elseif ($destinataireRole == RolesEnum::Directeur_general()->value) {

                $destinataire = User::Role([RolesEnum::Directeur_general()->value, RolesEnum::Interimaire_DG()->value]);
                Notification::make()
                    ->title('Nouvelle demande')
                    ->body('Demande de réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . '')
                    ->actions([
                        NotificationActions::make('voir')
                            ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                            ->button()
                            ->color('primary'),
                    ])
                    ->sendToDatabase($destinataire);

                try {

                    Mail::to($destinataire)->send(new ReparationMail($this->record));

                } catch (\Exception $e) {

                    Notification::make('erreur')
                        ->body("Erreur lors de l'envoi du mail au validateur. Veuillez le contacter pour l'informer")
                        ->send();
                }
            } elseif (
                in_array($destinataireRole, [
                    RolesEnum::Directeur_general()->value,
                    RolesEnum::Interimaire_DG()->value,
                    RolesEnum::Diga()->value,
                    RolesEnum::Chef_parc()->value,
                    RolesEnum::Budget()->value,
                ])
            ) {

                $realDestination = User::role($destinataireRole)->get();
                Notification::make()
                    ->title('Nouvelle demande')
                    ->body('Demande de réparation pour l\'engin immatriculé ' . $concernedEngine->plate_number . '')
                    ->actions([
                        NotificationActions::make('voir')
                            ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                            ->button()
                            ->color('primary'),
                    ])
                    ->sendToDatabase($realDestination);

                try {
                    Mail::to($destinataire)->send(new ReparationMail($this->record));
                } catch (\Exception $e) {
                    Notification::make('erreur')
                        ->body("Erreur lors de l'envoi du mail au validateur. Veuillez le contacter pour l'informer")
                        ->send();
                }
            }
            ;
        }

    }


    protected function handleRecordCreation(array $data): Model
    {

        $engineCircuit = Engine::find($data["engine_id"])->circuit_id;

        
            $data = [
                ...$data,
                "circuit_id" => $engineCircuit
            ];
         
 
        
        return static::getModel()::create($data);
    }
}
