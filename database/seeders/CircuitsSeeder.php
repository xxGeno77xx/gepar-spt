<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Circuit;
use Illuminate\Database\Seeder;
use App\Support\Database\RolesEnum;
use App\Support\Database\CircuitsEnums;

class CircuitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

       $chefDiviionID = Role::where("name", RolesEnum::Chef_division()->value)->first()->id;
       $budgetID = Role::where("name", RolesEnum::Budget()->value)->first()->id;
       $directeurID = Role::where("name", RolesEnum::Directeur()->value)->first()->id;
       $digaID = Role::where("name", RolesEnum::Diga()->value)->first()->id;
       $dG = Role::where("name", RolesEnum::Directeur_general()->value)->first()->id;
       $chefParcID = Role::where("name", RolesEnum::Chef_parc()->value)->first()->id;
       $dpl = Role::where("name", RolesEnum::Dpl()->value)->first()->id;
  

        
        $circuitDeDivision = [
            
            ['role_id' => $dpl], // Dpl
            ['role_id' => $chefDiviionID], // Chef division
            ['role_id' => $budgetID], // Budget
            ['role_id' => $directeurID], // Directeur
            ['role_id' => $digaID], // DIGA
            ['role_id' => $dG], // Directeur général
            ['role_id' => $budgetID], // Budget
            ['role_id' => $chefParcID], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_division()->value,
            'steps' => $circuitDeDivision,
        ]);

        $circuitdeDirection = [

            ['role_id' => $dpl], // Dpl
            ['role_id' => $budgetID], // Budget
            ['role_id' => $directeurID], // Directeur
            ['role_id' => $digaID], // DIGA
            ['role_id' => $dG], // Directeur général
            ['role_id' => $budgetID], // Budget
            ['role_id' => $chefParcID], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_direction()->value,
            'steps' => $circuitdeDirection,
        ]);

        $circuitDeLaDirectionGenerale = [

            ['role_id' => $dpl], // Dpl
            ['role_id' => $budgetID], // Budget
            ['role_id' => $digaID], // DIGA
            ['role_id' => $dG], // Directeur général
            ['role_id' => $budgetID], // Budget
            ['role_id' => $chefParcID], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_de_la_direction_generale()->value,
            'steps' => $circuitDeLaDirectionGenerale,
        ]);

        $circuitParticulier = [  // circuits où  le DG est à la fois le directeur de département

            ['role_id' => $dpl], // Dpl
            ['role_id' => $chefDiviionID], // Chef division
            ['role_id' => $budgetID], // Budget
            ['role_id' => $digaID], // DIGA
            ['role_id' => $dG], // Directeur général
            ['role_id' => $budgetID], // Budget
            ['role_id' => $chefParcID], // Chef parc
        ];

        Circuit::create([
            'name' => CircuitsEnums::circuit_particulier()->value,
            'steps' => $circuitParticulier,
        ]);

    }
}
