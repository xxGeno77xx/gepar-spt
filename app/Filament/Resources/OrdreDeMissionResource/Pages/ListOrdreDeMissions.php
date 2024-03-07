<?php

namespace App\Filament\Resources\OrdreDeMissionResource\Pages;

use App\Filament\Resources\OrdreDeMissionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrdreDeMissions extends ListRecords
{
    protected static string $resource = OrdreDeMissionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join('chauffeurs', 'chauffeurs.id', 'ordre_de_missions.chauffeur_id')
            ->select('ordre_de_missions.*', 'chauffeurs.fullname as chauffeur');
    }
}
