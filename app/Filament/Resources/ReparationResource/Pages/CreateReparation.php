<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Filament\Resources\ReparationResource;
use App\Models\Circuit;
use App\Models\Departement;
use App\Models\DepartementUser;
use App\Models\Engine;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use App\Support\Database\PermissionsClass;
use App\Support\Database\ReparationValidationStates;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Notifications\Actions\Action as NotificationActions;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

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
            if (! $latestReparation->date_fin) {
                Notification::make()
                    ->warning()
                    ->title('Attention!')
                    ->body('La réparation précédente n\'est pas encore terminée.Veuillez la compléter avant d\'en renseigner une nouvelle!!!')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        if (! is_null($newRaparation['date_fin'])) {

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

        $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

        foreach ($userCentresCollection as $userCentre) {
            $userCentresIds[] = $userCentre->departement_code_centre;
        }

        $dirGeneDivisions = [
            Departement::where('sigle_centre', 'CI')->first()->code_centre,
            Departement::where('sigle_centre', 'DSC')->first()->code_centre,
            // Departement::where('sigle_centre', 'DFC')->first()->code_centre,  conformité
        ];

        if ((auth()->user()->hasAnyRole([RolesEnum::Chef_Division()->value, RolesEnum::Delegue_Division()->value])) && (array_intersect($userCentresIds, $dirGeneDivisions))) {

            $data['circuit_id'] = 4; // circuit particulier

        } elseif (auth()->user()->hasAnyRole([RolesEnum::Directeur_general()->value, RolesEnum::Delegue_Direction_Generale()->value])) {

            $data['circuit_id'] = 3; // circuit de  Direction Générale
        } elseif (auth()->user()->hasAnyRole([RolesEnum::Directeur()->value, RolesEnum::Delegue_Direction()->value])) {

            $data['circuit_id'] = 2; // circuit de Direction

        } elseif (auth()->user()->hasAnyRole([RolesEnum::Chef_Division()->value, RolesEnum::Delegue_Division()->value, RolesEnum::Chef_parc()->value])) {

            $data['circuit_id'] = 1; // circuit de Division
        }

        $circuit = Circuit::where('id', $data['circuit_id'])->first()->steps;

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

        $destinataire = User::role($destinataireRole)->first();

        if ($destinataire) {

            if (in_array($destinataireRole, [RolesEnum::Directeur()->value, RolesEnum::Chef_division()->value]) && $destinataire->departement_id == $concernedEngine->departement_id) {

                $realDestination = User::role($destinataireRole)->where('departement_id', $concernedEngine->departement_id)->first();

                Notification::make()
                    ->title('Nouvelle demande')
                    ->body('Demande de réparation pour l\'engin immatriculé '.$concernedEngine->plate_number.'')
                    ->actions([
                        NotificationActions::make('voir')
                            ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                            ->button()
                            ->color('primary'),
                    ])
                    ->sendToDatabase($realDestination);

            } elseif ($destinataireRole == RolesEnum::Directeur_general()->value) {
                Notification::make()
                    ->title('Nouvelle demande')
                    ->body('Demande de réparation pour l\'engin immatriculé '.$concernedEngine->plate_number.'')
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
