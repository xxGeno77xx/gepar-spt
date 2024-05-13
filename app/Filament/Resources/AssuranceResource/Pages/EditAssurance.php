<?php

namespace App\Filament\Resources\AssuranceResource\Pages;

use App\Filament\Resources\AssuranceResource;
use App\Models\Assurance;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Database\Eloquent\Builder;

class EditAssurance extends EditRecord
{
    protected static string $resource = AssuranceResource::class;

    protected function getActions(): array
    {
        // if (auth()->user()->hasAnyPermission([PermissionsClass::assurances_delete()->value])) {
        //     return [
        //         // Actions\DeleteAction::make(),
        //         Actions\Action::make('Supprimer')
        //             ->color('danger')
        //             ->icon('heroicon-o-eye-off')
        //             ->action(function (?Assurance $record) {
        //                 $this->record->update(['state' => StatesClass::Deactivated()->value]);
        //                 redirect('/assurances');
        //                 Notification::make()
        //                     ->title('Supprimé(e)')
        //                     ->success()
        //                     ->persistent()
        //                     ->send();
        //             })
        //             ->requiresConfirmation(),

        //     ];

        // }

        return [];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::assurances_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        $assurance = $this->record;

        $assurance->update(['updated_at_user_id' => auth()->user()->id]);
    }

    protected function beforeSave(): void
    {
        $assurance = $this->data;

        if (Carbon::parse($assurance['date_fin'])->format('y-m-d') <= Carbon::parse($assurance['date_debut'])->addMonths(1)->format('y-m-d')) {
            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('L\'assurance doit durer au minimum 1 mois!!!')
                ->persistent()
                ->send();

            $this->halt();
        }

        //Retrieving all records that match dates within form
        $matchingAssurances = Assurance::select(['date_debut', 'date_fin'])
            ->where('assurances.state', StatesClass::Activated()->value)
            ->where('engine_id', $assurance['engine_id'])
            ->Where(function (Builder $query) {

                $assurance = $this->data;

                $query->orWhere('assurances.date_debut', carbon::parse($assurance['date_debut'])->format('Y-m-d'))
                    ->orWhere('assurances.date_fin', carbon::parse($assurance['date_fin'])->format('Y-m-d'));
            })
            ->get();

        if ($matchingAssurances->count() > 1) {
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
