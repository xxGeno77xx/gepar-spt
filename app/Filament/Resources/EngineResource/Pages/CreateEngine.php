<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use App\Models\Chauffeur;
use App\Models\Engine;
use App\Support\Database\PermissionsClass;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEngine extends CreateRecord
{
    protected static ?string $title = 'Ajouter un engin';

    protected static string $resource = EngineResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_create()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // public function afterCreate()
    // {
    //     $data = $this->data;

    //     if (!is_null($data["chauffeur_id"])) {
    //         Chauffeur::find($data['chauffeur_id'])->update([
    //             "engine_id" => Engine::where('plate_number', $data["plate_number"])->value('id')
    //         ]);
    //     }
    // }

    // public function beforeCreate()
    // {
    //     $data = $this->data;

    //     if (!is_null($data["chauffeur_id"])) {
    //         $chauffeur = Chauffeur::find($data['chauffeur_id']);

    //         if ($chauffeur) {

    //             if (!is_null($chauffeur->engine_id)) {
    //                 Notification::make()
    //                     ->warning()
    //                     ->title('Attention!')
    //                     ->body("Le chauffeur choisi est déjà associé à un engin")
    //                     ->persistent()
    //                     ->send();

    //                 $this->halt();
    //             }
    //         }
    //     }
    // }
}
