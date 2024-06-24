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
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 5], // Chef division
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_division()->value,
            'steps' => $circuitDeDivision,
        ]);

        $circuitdeDirection = [

            ['role_id' => 7],
            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 8],
            ['role_id' => 7],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_direction()->value,
            'steps' => $circuitdeDirection,
        ]);

        $circuitDeLaDirectionGenerale = [

            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 8],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_la_direction_generale()->value,
            'steps' => $circuitDeLaDirectionGenerale,
        ]);

        $circuitParticulier = [  // circuits où  le DG est à la fois le directeur de département

            ['role_id' => 5],
            ['role_id' => 9],
            ['role_id' => 6],
            ['role_id' => 5],
            ['role_id' => 8],
            ['role_id' => 9],
            ['role_id' => 8],
            ['role_id' => 6],
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_particulier()->value,
            'steps' => $circuitParticulier,
        ]);

        //======circuits avec DIGA=========================

        //////Circuit de division/////////

        $circuitDeDivisionaAvecDIGA_dir = [
            ['role_id' => 5], // Chef division
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 5], // Chef division
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 13], // DIGA
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_division_diga_dir()->value,
            'steps' => $circuitDeDivisionaAvecDIGA_dir,
        ]);

        $circuitDeDivisionaAvecDIGA_dg = [
            ['role_id' => 5], // Chef division
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 5], // Chef division
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_division_diga_dg()->value,
            'steps' => $circuitDeDivisionaAvecDIGA_dg,
        ]);

        //////Circuit de direction/////////

        $circuitdeDirectionAvecDIGA_dir = [

            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 13], // DIGA
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_direction_diga_dir()->value,
            'steps' => $circuitdeDirectionAvecDIGA_dir,
        ]);

        $circuitdeDirectionAvecDIGA_dg = [

            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 8], // Budget
            ['role_id' => 7], // Directeur
            ['role_id' => 9], // Directeur général
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_direction_diga_dg()->value,
            'steps' => $circuitdeDirectionAvecDIGA_dg,
        ]);

        //////Circuit de la Direction Générale/////////

        $circuitDeLaDirectionGeneraleAvecDIGA = [

            ['role_id' => 9], // Directeur général
            ['role_id' => 6], // Chef parc
            ['role_id' => 8], // Budget
            ['role_id' => 9], // Directeur général
            ['role_id' => 13], // DIGA
            ['role_id' => 9], // Directeur général
            ['role_id' => 8], // Budget
            ['role_id' => 6], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_la_direction_generale_diga()->value,
            'steps' => $circuitDeLaDirectionGeneraleAvecDIGA,
        ]);

        /////circuit particulier//////

        $circuitParticulierAvecDIGA = [  // circuits où  le DG est à la fois le directeur de département

            ['role_id' => 5],  // Chef division
            ['role_id' => 9],  // Directeur général
            ['role_id' => 6],  // Chef parc
            ['role_id' => 5],  // Chef division
            ['role_id' => 8],  // Budget
            ['role_id' => 9],  // Directeur général
            ['role_id' => 13],  // DIGA
            ['role_id' => 9],  // Directeur général
            ['role_id' => 8],  // Budget
            ['role_id' => 6],  // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_particulier_diga()->value,
            'steps' => $circuitParticulierAvecDIGA,
        ]);

    }
}
