<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use App\Models\Visite;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Database\Eloquent\Builder;

class EditVisite extends EditRecord
{
    protected static string $resource = VisiteResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::visites_delete()->value])) {
            return [
                // Actions\DeleteAction::make(),
                Actions\Action::make('Supprimer')
                    ->color('danger')
                    ->action(function (?Visite $record) {
                        $this->record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/visites');
                        Notification::make()
                            ->title('Supprimé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->requiresConfirmation(),

            ];

        }

        return [];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::visites_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function afterSave()
    {
        $visite = $this->record;

        $visite->update(['updated_at_user_id' => auth()->user()->id]);
    }

    protected function beforeSave(): void
    {  //getting record data: using $this->data because  $this->record is null at this point of creation process? just a guess
        $Visite = $this->data;

        if (Carbon::parse($Visite['date_expiration']) <= Carbon::parse($Visite['date_initiale'])->addYear()->subDays(1)) {
            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('La visite technique doit durer au minimum 1 an!!!')
                ->persistent()
                ->send();
            $this->halt();
        }

        //visites that matching dates with the dates within form data
        $matchingVisites = Visite::select(['visites.date_initiale', 'visites.date_expiration'])
            ->where('visites.state', StatesClass::Activated())
            ->where('engine_id', $Visite['engine_id'])
            ->Where(function (Builder $query) {

                $Visite = $this->data;

                $query->orWhere('visites.date_initiale', carbon::parse($Visite['date_initiale'])->format('Y-m-d'))
                    ->orWhere('visites.date_expiration', carbon::parse($Visite['date_expiration'])->format('Y-m-d'));
            })
            ->get();

        if ($matchingVisites->count() > 1) {

            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('Il existe déjà une visite avec les dates fournies pour l\'enregistrement. Veuillez les changer!!!')
                ->persistent()
                ->send();

            $this->halt();

        }

    }
}
