<?php

namespace Database\Seeders;

use App\Models\Circuit;
use Illuminate\Database\Seeder;

class CircuitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $circuitDeDivision = [
            ['role_id' => 5], // Chef division
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 13], // Diga
            ['role_id' => 6], // Chef parc
            ['role_id' => 5], // Chef division
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => 'Circuit de Division',
            'steps' => $circuitDeDivision,
        ]);

        $circuitdeDirection = [

            ['role_id' => 7],
            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 13],
            ['role_id' => 6],
            ['role_id' => 8],
            ['role_id' => 7],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => 'Circuit de Direction',
            'steps' => $circuitdeDirection,
        ]);

        $circuitDeLaDirectionGenerale = [

            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 13],
            ['role_id' => 6],
            ['role_id' => 8],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => 'Circuit de la Direction Générale',
            'steps' => $circuitDeLaDirectionGenerale,
        ]);

        $circuitParticulier = [  // circuits où  le DG est à la fois le directeur de département

            ['role_id' => 5],
            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 13],
            ['role_id' => 6],
            ['role_id' => 5],
            ['role_id' => 8],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => 'Circuit particulier',
            'steps' => $circuitParticulier,
        ]);
    }
}
