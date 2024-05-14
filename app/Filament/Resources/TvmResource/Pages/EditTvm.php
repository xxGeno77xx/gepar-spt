<?php

namespace App\Filament\Resources\TvmResource\Pages;

use Carbon\Carbon;
use App\Models\Tvm;
use Filament\Pages\Actions;
use App\Support\Database\StatesClass;
use App\Filament\Resources\TvmResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Database\Eloquent\Builder;

class EditTvm extends EditRecord
{
    protected static string $resource = TvmResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function beforeSave()
    {
        $tvm = $this->record;

        if (Carbon::parse($tvm['date_fin']) <= Carbon::parse($tvm['date_debut'])->addYear()->subDays(1)) {
            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('La TVM doit durer au minimum 1 an!!!')
                ->send();

            $this->halt();
        }

         //tvms that matching dates with the dates within form data
         $matchingVisites = Tvm::select(['tvms.date_debut', 'tvms.date_fin'])
         ->where('tvms.state', StatesClass::Activated()->value)
         ->where('engine_id', $tvm['engine_id'])
         ->Where(function (Builder $query){

             $tvmData = $this->data;

             $query->orWhere('tvms.date_debut', carbon::parse($tvmData['date_debut'])->format('Y-m-d'))
                 ->orWhere('tvms.date_fin', carbon::parse($tvmData['date_fin'])->format('Y-m-d'));
         })
         ->get();

     if ($matchingVisites->count() >= 1) {

         Notification::make()
             ->warning()
             ->title('Attention!')
             ->body('Il existe déjà une TVM avec les dates fournies pour l\'enregistrement. Veuillez les changer!!!')
             ->persistent()
             ->send();

         $this->halt();

     }
    }

    public function afterSave()
    {
        $this->record->update(['updated_at_user_id' => auth()->user()->id]);
    }


}
