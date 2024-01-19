<?php

namespace Database\Seeders;

use App\Models\TypeReparation;
use Illuminate\Database\Seeder;
use App\Support\Database\TypesReparation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TypeReparationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typesReparations = TypesReparation::toValues();

        foreach( $typesReparations as $key => $typeReparation)
        {
            TypeReparation::firstOrCreate([
                'libelle' =>  $typeReparation,
            ]);
        }
    }
}
