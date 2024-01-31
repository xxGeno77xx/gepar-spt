<?php

namespace App\Filament\Resources\ParametreResource\Pages;

use App\Filament\Resources\ParametreResource;
use App\Models\Parametre;
use App\Support\Database\PermissionsClass;
use Filament\Resources\Pages\EditRecord;

class EditParametre extends EditRecord
{
    protected static string $resource = ParametreResource::class;

    protected function getActions(): array
    {
        return [];
    }

    public function afterSave()
    {

        $choix = parametre::where('options', $this->record['options']);

        switch ($this->record->limite) {
            case Parametre::UN_MOIS:
                $choix->update(['nom' => parametre::UN_MOIS_VALUE]);
                break;
            case Parametre::DEUX_SEMAINES:
                $choix->update(['nom' => parametre::DEUX_SEMAINES_VALUE]);
                break;
            case Parametre::UNE_SEMAINE:
                $choix->update(['nom' => parametre::UNE_SEMAINE_VALUE]);
                break;
        }

    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::parametre_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
