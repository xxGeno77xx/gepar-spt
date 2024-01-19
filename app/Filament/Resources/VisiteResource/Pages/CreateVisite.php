<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Models\Engine;
use App\Models\Visite;
use Illuminate\Support\Carbon;
use Filament\Pages\Actions\Action;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use App\Filament\Resources\VisiteResource;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;

class CreateVisite extends CreateRecord
{
    protected static ?string $title = 'Ajouter une visite technique';

    protected static string $resource = VisiteResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::visites_create()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function afterCreate(): void
    {
        $visite = $this->record;

        Engine::where('id',$visite->engine_id)->update(["Visites_mail_sent"=>false]);
    }
    

    protected function beforeCreate(): void
    {  //getting record data: using $this->data because  $this->record is null at this point of creation process? just a guess
       $Visite = $this->data;

        //getting latest visite for given engine, serves as check  to see if there's a visit or not
       $latestVisiteForThisEngine=Visite::where('engine_id',$Visite['engine_id'])
       ->where('visites.state',StatesClass::Activated())
       ->whereNull('deleted_at')
       ->orderBy('created_at','desc')
       ->first();



         if(Carbon::parse($Visite['date_expiration'])<=Carbon::parse($Visite['date_initiale'])->addYear()->subDays(1))
        {
            Notification::make()
            ->warning()
            ->title('Attention!')
            ->body('La visite technique doit durer au minimum 1 an!!!')
            ->persistent()
            ->send();
            $this->halt();
        }

       if($latestVisiteForThisEngine)
        {       
            //formatting date to YMD format
            $latestCarbonVisiteForThisEngine = Carbon::parse( $latestVisiteForThisEngine['date_expiration']);
            // dd($latestCarbonVisiteForThisEngine->format('d-m-y'),carbon::today()->subDays(2)->format('d-m-y'));

            // dd($latestCarbonVisiteForThisEngine,carbon::today()->subDays(2) );

            if(($latestCarbonVisiteForThisEngine)->subDays(2) > (carbon::today()) )
                {
                    Notification::make()
                        ->warning()
                        ->title('Attention!')
                        ->body('La Visite précédente n\'a pas encore expiré. Vous ne pouvez pas en enregistrer de nouvelle!')
                        ->persistent()
                        ->send();

                        $this->halt();
                }

            //get all visites for the given engine
            $allVisitesForThisEngine=Visite::select('date_initiale', 'date_expiration')->where('visites.state',StatesClass::Activated())->where('engine_id',$Visite['engine_id'])->get();

            //parse dates to carbon instance
            $carbonVisiteDateExpiration=Carbon::parse($Visite['date_expiration'])->format('y-m-d');

            //parse dates to catbon instances
            $carbonVisiteDateInitiale=Carbon::parse($Visite['date_initiale'])->format('y-m-d'); 

            //loop through all visites for the given engine
            foreach($allVisitesForThisEngine as $engineVisite)

            {   //parsing  end date to carbon
                $carbonVisiteExpiration=Carbon::parse($engineVisite->date_expiration)->format('y-m-d');

                //parsing start date to carbon
                $carbonVisiteInitiale=Carbon::parse($engineVisite->date_initiale)->format('y-m-d');

                //checking if there is an existing entry with given dates for new entry
                if(($carbonVisiteExpiration == $carbonVisiteDateExpiration) || ($carbonVisiteInitiale == $carbonVisiteDateInitiale))
                    {
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

    }

    protected function getRedirectUrl(): string
    {
     return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            "user_id"=>auth()->user()->id
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter '))
            ->submit('create')
            ->keyBindings(['mod+s']);
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
