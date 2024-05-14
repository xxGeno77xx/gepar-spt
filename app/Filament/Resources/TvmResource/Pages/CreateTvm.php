<?php

namespace App\Filament\Resources\TvmResource\Pages;

use Carbon\Carbon;
use App\Models\Tvm;
use App\Models\Engine;
use Filament\Pages\Actions;
use App\Support\Database\StatesClass;
use App\Filament\Resources\TvmResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;

class CreateTvm extends CreateRecord
{
    protected static string $resource = TvmResource::class;

    protected static ?string $title = 'Ajouter une TVM';

    protected function handleRecordCreation(array $data): Model
    {


        foreach ($data["engins_prix"] as $key => $engine) {
            //if forelast then break 
            if ($key == count($data["engins_prix"]) - 1) {

                break;

            }

            $model = static::getModel()::create([
                "date_debut" => $data["date_debut"],
                "date_fin" => $data["date_fin"],
                "reference" => $data["reference"],
                "engine_id" => intval($engine["engine_id"]),
                "prix" => intval($engine["prix"]),
                "user_id" => $data["user_id"],
                "updated_at_user_id" => $data["updated_at_user_id"],
                "state" => StatesClass::Activated()->value
            ]);

        }

        return static::getModel()::create([
            "date_debut" => $data["date_debut"],
            "date_fin" => $data["date_fin"],
            "reference" => $data["reference"],
            "engine_id" => intval($data["engins_prix"][count($data["engins_prix"]) - 1]["engine_id"]),
            "prix" => intval($data["engins_prix"][count($data["engins_prix"]) - 1]["prix"]),
            "user_id" => $data["user_id"],
            "updated_at_user_id" => $data["updated_at_user_id"],
            "state" => StatesClass::Activated()->value
        ]);

    }


    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::assurances_create()->value]);

        abort_if(!$userPermission, 403, __("Vous n'avez pas acces à cette fonctionnalité"));
    }


    protected function afterCreate(): void
    {
        $tvm = $this->record;

        Engine::where('id', $tvm->engine_id)->update(['tvm_mail_sent' => false]);
    }

    protected function beforeCreate(): void
    {
        $tvm = $this->data;

        $enginesForThisTVM = $tvm["engins_prix"];

        $enginesIDs = [];

        $latestTvmsForTheseEngines = [];

        $allTvmsForEnginesInForm = [];

        // get latest tvms for the engines in the form
        foreach ($enginesForThisTVM as $engine) { 

            $enginesIDs [] = intval($engine["engine_id"]);

            $latestTvmsForTheseEngines[] = Tvm::where("engine_id", $engine["engine_id"])
                ->where('tvms.state', StatesClass::Activated()->value)
                ->orderBy('id', 'desc')
                ->first();     
        }

        $allTvmsForEnginesInForm = Tvm::whereIn("engine_id", $enginesIDs)
        ->where('tvms.state', StatesClass::Activated()->value)
        ->get();


        //collection of latest tvms for engines in form
        $tvmCollection = collect(($latestTvmsForTheseEngines));

        if ($tvmCollection) {

            foreach ($tvmCollection as $aTvmForAgivenEngine) {

                if ($aTvmForAgivenEngine) {

                    $latestTvmEndDateForThisEngine = Carbon::parse($aTvmForAgivenEngine->date_fin);

                    if ($latestTvmEndDateForThisEngine) { 
                          //if end_date is < to current date minus 2 days, notification + stopping process
                        if (($latestTvmEndDateForThisEngine)->subDays(2) > carbon::today()) {
                            Notification::make()
                                ->warning()
                                ->title('Attention!')
                                ->body('L\'engin immatriculé '.Engine::where("id", $aTvmForAgivenEngine->engine_id)->first()->plate_number.' possède une TVM qui n\'a pas encore expiré. Vous ne pouvez pas en enregistrer de nouvelle pour cet engin là!')
                                ->persistent()
                                ->send();

                            $this->halt();
                        }

                    }
                }

            }

            foreach($allTvmsForEnginesInForm as $tvmItem)
            {
                $carbonTvmRecordDateExpiration = Carbon::parse($tvmItem['date_fin'])->format('y-m-d');

                $carbonTvmRecordDateInitiale = Carbon::parse($tvmItem['date_debut'])->format('y-m-d');

                $carbonTvmFormDataDateExpiration = Carbon::parse($tvm['date_fin'])->format('y-m-d');

                $carbonTvmFormDataDateInitiale = Carbon::parse($tvm['date_debut'])->format('y-m-d');
                

                if (($carbonTvmFormDataDateExpiration == $carbonTvmRecordDateExpiration) || ($carbonTvmFormDataDateInitiale == $carbonTvmRecordDateInitiale)) {
                    Notification::make()
                        ->warning()
                        ->title('Attention!')
                        ->body('Il existe déjà une TVM avec les dates fournies pour l\'engin '.Engine::where("id", $tvmItem->engine_id)->first()->plate_number.'. Veuillez les changer!!!')
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
            'user_id' => auth()->user()->id,
        ];
    }

}

