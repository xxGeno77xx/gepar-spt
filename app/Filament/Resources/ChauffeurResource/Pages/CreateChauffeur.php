<?php

namespace App\Filament\Resources\ChauffeurResource\Pages;

use App\Filament\Resources\ChauffeurResource;
use App\Models\AffectationChauffeur;
use App\Models\Chauffeur;
use App\Support\Database\PermissionsClass;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateChauffeur extends CreateRecord
{
    protected static string $resource = ChauffeurResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Chauffeurs_create()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('Ajouter '))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    public function afterCreate()
    {
        if ($this->data['engine_id']) {
            AffectationChauffeur::firstOrCreate([
                'chauffeur_id' => Chauffeur::orderBy('id', 'desc')->first()->id,
                'old_engine_id' => null,
                'new_engine_id' => $this->data['engine_id'],
                'date_affectation' => today(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
