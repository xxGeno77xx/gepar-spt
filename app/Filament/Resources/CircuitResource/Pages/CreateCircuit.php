<?php

namespace App\Filament\Resources\CircuitResource\Pages;

use App\Models\Circuit;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CircuitResource;

class CreateCircuit extends CreateRecord
{
    protected static string $resource = CircuitResource::class;

    public function beforeCreate()
    {
        // $f = Circuit::first()->value("steps");
        
        // foreach ($f as $item) {
           
        //     $roleIds[] = $item['roles'];
        // }

        // dd($roleIds);
    }
}
