<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Filament\Resources\ReparationResource;
use App\Models\Engine;
use App\Models\Reparation;
use App\Models\TypeReparation;
use App\Models\User;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use App\Support\Database\TypesReparation;
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

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
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
            ->where('engine_id', $newRaparation['engine_id'])
            ->where('state', StatesClass::Activated()->value)
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
        $id = TypeReparation::where('libelle', '=', TypesReparation::Revision_simple()->value)->value('id');

        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        if (in_array($id, $this->data['révisions'])) {

            $concernedEngine->update(['remainder' => 0]);

        }

        if (is_null($this->record['date_fin'])) {
            $concernedEngine->update([
                'state' => StatesClass::Repairing()->value,
            ]);
        }

        Notification::make()
            ->title('Nouvelle réparation')
            ->body('Demande de réparation pour l\'engin immatriculé '.$concernedEngine->plate_number.'')
            ->actions([
                NotificationActions::make('voir')
                    ->url(route('filament.resources.reparations.edit', $this->record->id), shouldOpenInNewTab: true)
                    ->button()
                    ->color('primary'),
            ])
            ->send()
            ->sendToDatabase(User::first());

    }
}
