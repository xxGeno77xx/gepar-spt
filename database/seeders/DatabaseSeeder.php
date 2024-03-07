<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CarburantsSeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(ModelesSeeder::class);
        $this->call(ParametersSeeder::class);
        $this->call(TypesSeeder::class);
        $this->call(TypeReparationSeeder::class);
        $this->call(ChauffeurSeeder::class);

        $this->call(RolesPermissionsSeeder::class);
        $this->call(DepartementsSeeder::class);

        $this->call(EnginesSeeder::class);

        // $this->call(PrestataireSeeder::class);
        $this->call(CircuitsSeeder::class);
        $this->call(ConsommationSeeder::class);



    }
}
