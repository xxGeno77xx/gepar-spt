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
        $types = TypesClass::toValues();

        foreach ($types as $key => $type) {
            Type::firstOrCreate([
                'nom_type' => $type,
                'state' => StatesClass::Activated()->value,
            ]);
        }
                
    }
}
