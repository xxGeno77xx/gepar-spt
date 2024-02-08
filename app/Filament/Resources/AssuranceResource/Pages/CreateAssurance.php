<?php

namespace App\Filament\Resources\AssuranceResource\Pages;

use App\Filament\Resources\AssuranceResource;
use App\Models\Assurance;
use App\Models\Engine;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateAssurance extends CreateRecord
{
    protected static ?string $title = 'Ajouter une assurance';

    protected static string $resource = AssuranceResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::assurances_create()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas acces à cette fonctionnalité"));
    }

    protected function afterCreate(): void
    {
        $assurance = $this->record;

        Engine::where('id', $assurance->engine_id)->update(['assurances_mail_sent' => false]);
    }

    protected function beforeCreate(): void
    {
        $assurance = $this->data;

        $latestAssuranceForThisEngine = Assurance::where('engine_id', $assurance['engine_id'])
            ->where('assurances.state', StatesClass::Activated()->value)
            ->orderBy('id', 'desc')
            ->first();

        if (Carbon::parse($assurance['date_fin'])->format('y-m-d') <= Carbon::parse($assurance['date_debut'])->addMonths(1)->format('y-m-d')) {
            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('L\'assurance doit durer au minimum 1 mois!!!')
                ->persistent()
                ->send();

            $this->halt();
        }

        if ($latestAssuranceForThisEngine) {
            $latestCarbonAssuranceForThisEngine = Carbon::parse($latestAssuranceForThisEngine['date_fin']);

            if ($latestCarbonAssuranceForThisEngine) {   //if end_date is < to current date minus 2 days, notification + stopping process
                if (($latestCarbonAssuranceForThisEngine)->subDays(2) > carbon::today()) {
                    Notification::make()
                        ->warning()
                        ->title('Attention!')
                        ->body('L\'assurance précédente n\'a pas encore expiré. Vous ne pouvez pas en enregistrer de nouvelle!')
                        ->persistent()
                        ->send();

                    $this->halt();
                }

                //get all Assurances for the given engine
                $allAssurancesForThisEngine = Assurance::select('date_debut', 'date_fin')
                    ->where('engine_id', $assurance['engine_id'])
                    ->where('assurances.state', StatesClass::Activated()->value)->get();

                //parse dates to carbon instance
                $carbonAssuranceDateExpiration = Carbon::parse($assurance['date_fin'])->format('y-m-d');

                //parse dates to catbon instances
                $carbonAssuranceDateInitiale = Carbon::parse($assurance['date_debut'])->format('y-m-d');

                //loop through all Assurances for the given engine
                foreach ($allAssurancesForThisEngine as $engineAssurance) {   //parsing  end date to carbon
                    $carbonAssuranceExpiration = Carbon::parse($engineAssurance->date_fin)->format('y-m-d');

                    //parsing start date to carbon
                    $carbonAssuranceInitiale = Carbon::parse($engineAssurance->date_debut)->format('y-m-d');

                    //checking if there is an existing entry with given dates for new entry
                    if (($carbonAssuranceExpiration == $carbonAssuranceDateExpiration) || ($carbonAssuranceInitiale == $carbonAssuranceDateInitiale)) {
                        Notification::make()
                            ->warning()
                            ->title('Attention!')
                            ->body('Il existe déjà une assurance avec les dates fournies pour l\'enregistrement. Veuillez les changer!!!')
                            ->persistent()
                            ->send();

                        $this->halt();
                    }
                }
            }
        }

    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'user_id' => auth()->user()->id,
        ];
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('filament::resources/pages/create-record.form.actions.create_another.label'))
            ->action('createAnother')
            ->label('Ajouter & ajouter un(e) autre')
            ->keyBindings(['mod+shift+s'])
            ->color('secondary');
    }
}
