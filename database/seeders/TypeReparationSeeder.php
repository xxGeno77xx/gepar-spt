<?php

namespace Database\Seeders;

use App\Models\TypeReparation;
use App\Support\Database\TypesReparation;
use Illuminate\Database\Seeder;

class TypeReparationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typesReparations = TypesReparation::toValues();

        foreach ($typesReparations as $key => $typeReparation) {
            TypeReparation::firstOrCreate([
                'libelle' => $typeReparation,
            ]);
        }
    }
}
