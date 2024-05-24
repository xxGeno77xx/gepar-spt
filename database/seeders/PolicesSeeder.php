<?php

namespace Database\Seeders;

use App\Models\PoliceAssurance;
use Illuminate\Database\Seeder;

class PolicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $assureurs = [
            97, 9, 10,
        ];

        foreach ($assureurs as $assureur) {
            PoliceAssurance::Create([
                'numero_police' => null,
                'assureur_id' => $assureur,
            ]);
        }
    }
}
