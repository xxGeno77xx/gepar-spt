<?php

namespace App\Filament\Resources\TvmResource\Pages;

use App\Filament\Resources\TvmResource;
use App\Models\Engine;
use App\Models\Tvm;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTvm extends CreateRecord
{
    protected static string $resource = TvmResource::class;

    protected static ?string $title = 'Ajouter une TVM';

    protected function handleRecordCreation(array $data): Model
    {

        foreach ($data['engins_prix'] as $key => $engine) {
            //if forelast then break
            if ($key == count($data['engins_prix']) - 1) {

                break;

            }

            $model = static::getModel()::create([
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'reference' => intval($engine['reference']),
                'engine_id' => intval($engine['engine_id']),
                'prix' => intval($engine['prix']),
                'user_id' => $data['user_id'],
                'updated_at_user_id' => $data['updated_at_user_id'],
                'state' => StatesClass::Activated()->value,
            ]);

        }

        return static::getModel()::create([
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'reference' => intval($data['engins_prix'][count($data['engins_prix']) - 1]['reference']),
            'engine_id' => intval($data['engins_prix'][count($data['engins_prix']) - 1]['engine_id']),
            'prix' => intval($data['engins_prix'][count($data['engins_prix']) - 1]['prix']),
            'user_id' => $data['user_id'],
            'updated_at_user_id' => $data['updated_at_user_id'],
            'state' => StatesClass::Activated()->value,
        ]);

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::assurances_create()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas acces à cette fonctionnalité"));
    }

    protected function afterCreate(): void
    {

        $data = $this->data;

        $this->resetTvmMailSentCollumnOnEnginesTable($data);

    }

    protected function beforeCreate(): void
    {
        $data = $this->data;

        $this->checkIfEngineAlreadyHasTvmForThisYear($data);

        $this->preventFutureDate($data);
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

    public function checkIfEngineAlreadyHasTvmForThisYear($data)
    {
        $formEngines = $this->data['engins_prix'];

        $formYear = Carbon::parse($this->data['date_debut'])->format('Y');

        $engineIDs = [];

        foreach ($formEngines as $engine) {
            $engineIDs[] = $engine['engine_id'];
        }

        $allTvmsForEnginesInForm = Tvm::whereIn('engine_id', $engineIDs)->get();

        foreach ($allTvmsForEnginesInForm as $tvm) {
            if (Carbon::parse($tvm->date_debut)->format('Y') == $formYear) {
                Notification::make()
                    ->warning()
                    ->title('Attention!')
                    ->body('Il existe déjà une TVM de l\'année '.$formYear.' pour l\'engin '.Engine::where('id', $tvm->engine_id)->first()->plate_number)
                    ->send();

                $this->halt();
            }
        }
    }

    public function resetTvmMailSentCollumnOnEnginesTable($data)
    {
        $formEngines = $this->data['engins_prix'];

        $engineIDs = [];

        foreach ($formEngines as $engine) {
            $engineIDs[] = $engine['engine_id'];
        }

        foreach ($engineIDs as $iD) {
            Engine::where('id', $iD)->update(['tvm_mail_sent' => 0]);
        }
    }

    public function preventFutureDate($data)
    {
        $formYear = Carbon::parse($this->data['date_debut'])->format('Y');

        $currentYear = Carbon::parse(now())->format('Y');

        if ($formYear > $currentYear) {

            Notification::make()
                ->warning()
                ->title('Attention!')
                ->body('Vous essayez d\'enregistrer une TVM pour une année ultérieure à '.$currentYear.'!!!')
                ->send();

            $this->halt();
        }
    }
}
