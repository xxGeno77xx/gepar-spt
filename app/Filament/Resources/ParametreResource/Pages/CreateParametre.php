<?php

namespace App\Filament\Resources\ParametreResource\Pages;

use App\Models\Parametre;
use Filament\Pages\Actions;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ParametreResource;

class CreateParametre extends CreateRecord
{
    protected static string $resource = ParametreResource::class;
    protected static bool $canCreateAnother = false;

    // protected function afterCreate(): void

    // {
  
    //     $choix=parametre::orderBy('Created_at','desc');

    //     switch($this->record->limite)
    //     {
    //         case Parametre::UN_MOIS:
    //             $choix->update(["nom"=>parametre::UN_MOIS_VALUE]);
    //             break;
    //         case Parametre::DEUX_SEMAINES:   
    //             $choix->update(["nom"=>parametre::DEUX_SEMAINES_VALUE]);
    //             break;
    //         case Parametre::UNE_SEMAINE:
    //             $choix->update(["nom"=>parametre::UNE_SEMAINE_VALUE]);
    //             break;
    //     }

    //     //parametre::where('id','<>',$choix->value('id'))->delete();
    // }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::Parametre_update()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
     return $this->getResource()::getUrl('index');
    }

   
}
