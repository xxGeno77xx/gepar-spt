<?php

namespace Database\Seeders;

use App\Models\Carburant;
use Illuminate\Database\Seeder;
use App\Support\Database\CarburantsClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CarburantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carburants=CarburantsClass::toValues();

        foreach($carburants as $key=>$carburant)
        {
            Carburant::firstOrCreate([
                'type_carburant'=>$carburant,
            ]);
        }
    }
}
