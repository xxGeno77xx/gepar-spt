<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Support\Database\DepartementsClass;
use Illuminate\Database\Seeder;

class DepartementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departements = DepartementsClass::toValues();

        foreach ($departements as $key => $departement) {
            Departement::firstOrCreate([
                'nom_departement' => $departement,
            ]);
        }
    }
}
