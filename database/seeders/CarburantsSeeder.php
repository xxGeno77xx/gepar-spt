<?php

namespace Database\Seeders;

use App\Models\Carburant;
use App\Support\Database\CarburantsClass;
use App\Support\Database\StatesClass;
use Illuminate\Database\Seeder;

class CarburantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carburants = CarburantsClass::toValues();

        foreach ($carburants as $key => $carburant) {
            Carburant::firstOrCreate([
                'type_carburant' => $carburant,
                'state' => StatesClass::Activated()->value,
            ]);
        }
    }
}
