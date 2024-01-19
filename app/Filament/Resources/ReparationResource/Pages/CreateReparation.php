<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Models\Engine;

use App\Models\Reparation;
use Filament\Pages\Actions\Action;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ReparationResource;

class CreateReparation extends CreateRecord
{
    protected static ?string $title = 'Ajouter une réparation';

    protected static string $resource = ReparationResource::class;
    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_create()->value]);

        abort_if(!$userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

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
            ->where('engine_id', $newRaparation["engine_id"])
            ->where('state', StatesClass::Activated())
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

    // protected function getCreateAnotherFormAction(): Action
    // {
    //     return Action::make('createAnother')
    //         ->label(__('filament::resources/pages/create-record.form.actions.create_another.label'))
    //         ->action('createAnother')
    //         ->label('Ajouter & ajouter un(e) autre')
    //         ->keyBindings(['mod+shift+s'])
    //         ->color('secondary');
    // }

    public function afterCreate()
    {
        
        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        if (is_null($this->record['date_fin'])) {
            $concernedEngine->update([
                'state' => StatesClass::Repairing()->value
            ]);
        }

    }
}
