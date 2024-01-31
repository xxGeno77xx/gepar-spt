<?php

namespace Database\Seeders;

use App\Models\Type;
use App\Support\Database\StatesClass;
use App\Support\Database\TypesClass;
use Illuminate\Database\Seeder;

class TypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departements = TypesClass::toValues();

        foreach ($departements as $key => $departement) {
            Type::firstOrCreate([
                'nom_type' => $departement,
                'state' => StatesClass::Activated(),
            ]);
        }
    }
}
