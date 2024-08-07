<?php

namespace Database\Seeders;

use App\Models\Circuit;
use App\Support\Database\CircuitsEnums;
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
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_division()->value,
            'steps' => $circuitDeDivision,
        ]);

        $circuitdeDirection = [
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_direction()->value,
            'steps' => $circuitdeDirection,
        ]);

        $circuitDeLaDirectionGenerale = [

            ['role_id' => 8], // Budget
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_la_direction_generale()->value,
            'steps' => $circuitDeLaDirectionGenerale,
        ]);

        $circuitParticulier = [  // circuits où  le DG est à la fois le directeur de département

            ['role_id' => 5], // Chef division
            ['role_id' => 8], // Budget
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_particulier()->value,
            'steps' => $circuitParticulier,
        ]);

    }
}
