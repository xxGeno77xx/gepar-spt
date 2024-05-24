<?php

namespace App\Filament\Resources\ChauffeurResource\Pages;

use App\Filament\Resources\ChauffeurResource;
use App\Models\AffectationChauffeur;
use App\Models\Chauffeur;
use App\Models\Engine;
use App\Support\Database\PermissionsClass;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditChauffeur extends EditRecord
{
    protected $listeners = ['refreshAffectations' => 'refresh'];

    public function refresh()
    {

    }

    protected static string $resource = ChauffeurResource::class;

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::Chauffeurs_update()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function getActions(): array
    {

        return [];

    }

    public function beforeSave()
    {

        $chauffeur = $this->record;

        $oldEngine = $chauffeur->engine_id;

        $newEngine = $this->data['engine_id'];

        $previousOwner = Chauffeur::where('engine_id', $this->data['engine_id'])
            ->whereNot('id', $this->record->id)
            ->first();

        if ($previousOwner) {
            $previousOwner->update(['engine_id' => null]);
        }

        if ($this->data['engine_id'] != $this->record['engine_id']) {
            AffectationChauffeur::firstOrCreate([

                'chauffeur_id' => $chauffeur->id,
                'old_engine_id' => $oldEngine,
                'new_engine_id' => $newEngine,
                'date_affectation' => now(),
            ]);

            Notification::make()
                ->title('Réaffectation')
                ->iconColor('primary')
                ->body('L\'engin immatriculé '.Engine::where('id', $this->data['engine_id'])->first()->plate_number.' a été affecté à ce chauffeur ')
                ->icon('heroicon-o-chat-alt-2')
                ->persistent()
                ->send();
        }

        $this->refreshFormData(['engin_id']);
    }
}
