<?php

namespace App\Filament\Resources\OrdreDeMissionResource\Pages;

use App\Functions\Unaccent;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OrdreDeMissionResource;

class CreateOrdreDeMission extends CreateRecord
{
    protected static string $resource = OrdreDeMissionResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
       foreach($data["lieu"] as $lieu)
       {
        $temp[] = strtoupper( Unaccent::unaccent($lieu));
       };

       $data["lieu"] = $temp;

       return $data;
    }

    
}
