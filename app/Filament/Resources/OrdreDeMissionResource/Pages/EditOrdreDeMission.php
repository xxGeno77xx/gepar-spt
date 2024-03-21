<?php

namespace App\Filament\Resources\OrdreDeMissionResource\Pages;

use App\Filament\Resources\OrdreDeMissionResource;
use App\Functions\Unaccent;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdreDeMission extends EditRecord
{
    protected static string $resource = OrdreDeMissionResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        foreach ($data['lieu'] as $lieu) {
            $temp[] = strtoupper(Unaccent::unaccent($lieu));
        }

        return $data;
    }
}
