<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Models\Engine;
use App\Models\Reparation;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Hidden;
use Filament\Pages\Actions;
use Forms\Components\Textarea;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\RichEditor;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ReparationResource;
use App\Support\Database\ReparationValidationStates;

class EditReparation extends EditRecord
{
    protected static string $resource = ReparationResource::class;

    protected function getActions(): array
    {
        if (auth()->user()->hasAnyPermission([PermissionsClass::reparation_delete()->value])) {
            return [
            
            ];

        }

        return [];
    }

    protected function authorizeAccess(): void
    {
   
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::reparation_update()->value]);

        if($userPermission)
        {

            if($this->record->validation_state == "nextValue" || $this->record->validation_step == "100")
            {
                abort(403, "Vous ne pouvez plus modifier une réparation déjà achevée");
            }
            elseif( !in_array($this->record->validation_state, [null, ReparationValidationStates::Declaration_initiale()->value]))
            {
                abort_unless(($user->hasAnyRole([
                    RolesEnum::Chef_parc()->value,
                    RolesEnum::Dpl()->value,
                    RolesEnum::Budget()->value,
                 ]) && in_array($this->record->validation_state, [
                    ReparationValidationStates::Demande_de_travail_dg()->value,
                    ReparationValidationStates::Demande_de_travail_diga()->value,
                    ReparationValidationStates::Demande_de_travail_chef_parc()->value,
                 ])),403 ,"Vous n'avez pas les permissions pour modifier une demande en cours de validation");
            }

        }
       
        else abort(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    public function afterSave()
    {
        $reparation = $this->record;

        $concernedEngine = Engine::where('id', $this->record->engine_id)->first();

        $reparation->update(['updated_at_user_id' => auth()->user()->id]);

        if ($reparation->date_fin) {
            $concernedEngine->update([
                'state' => StatesClass::Activated()->value,
            ]);
        }
    }
}
