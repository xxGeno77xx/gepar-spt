<?php

namespace App\Filament\Resources\TvmResource\Pages;

use App\Filament\Resources\TvmResource;
use App\Models\Tvm;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

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
        $data = $this->data;

        $this->checkIfEngineAlreadyHasTvmForThisYear($data);
    }

    public function afterSave()
    {
        $this->record->update(['updated_at_user_id' => auth()->user()->id]);
    }

    public function checkIfEngineAlreadyHasTvmForThisYear($data)
    {

        $record = $this->record;

        $formYear = Carbon::parse($this->data['date_debut'])->format('Y');

        $allTvmsForThisEngine = Tvm::where('id', $record->engine_id)
            ->whereYear('date_debut', $formYear)
            ->whereNot('id', $record->id)
            ->get();

        if (count($allTvmsForThisEngine) > 1) {

            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('Il existe déjà une TVM de l\'année '.$formYear.' pour cet engin')
                ->send();

            $this->halt();
        }

    }
}
