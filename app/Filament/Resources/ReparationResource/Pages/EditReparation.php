<?php

namespace App\Filament\Resources\ReparationResource\Pages;

use App\Models\Role;
use App\Models\Engine;
use App\Models\Circuit;
use App\Models\Reparation;
use Filament\Pages\Actions;
use Forms\Components\Textarea;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
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



        $circuit = Circuit::where('id', $this->record->circuit_id)->value("steps");

        foreach ($circuit as $key => $item) {

            $roleIds[] = $item['role_id'];
        }

    $currentlyRequiredValidationRole = Role::where("id",  $roleIds[$this->record->validation_step]);


        $concernedEngine = Engine::where("id", $this->record->engine_id)->first();

        $user = auth()->user();

        if($userPermission)
        {

           
            if($this->record->validation_state == "nextValue" || $this->record->validation_step == "100")  // if reparation is finished
            {
                abort(403, "Vous ne pouvez plus modifier une réparation déjà achevée");
            }


            elseif( ($this->record->validation_step != 0)) // if is not in starting step
            {


                $chefParcRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                $firstOccurenceOfRole = array_search($chefParcRoleId, $roleIds); // first array key where role occurs
    
                $arrayKeys = array_keys($roleIds); //get array keys
    
                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);
    
    
                abort_unless($user->hasAnyRole([
                    Role::where("name", RolesEnum::Chef_parc()->value)->first()->name,
                    Role::where("name", RolesEnum::Dpl()->value)->first()->name,
                    Role::where("name", RolesEnum::Budget()->value)->first()->name,
                ]) && (in_array($this->record->validation_step, $indicesDesired)),
                403, 
                "Vous n'avez pas les permissions pour modifier une demande en cours de validation");
            }


            
        }
       
        else abort(403, __("Vous n'avez pas access à cette page"));
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
