<?php

namespace Database\Seeders;

use App\Models\Parametre;
use Illuminate\Database\Seeder;

class ParametersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $parametre = ParametersClass::toValues();

        // $options=["Assurances","Visites techniques"];

        // foreach ($parametre as $key => $nom) {
        parametre::firstOrCreate([
            'nom' => 'Rappels à 1 mois',
            'limite' => 30,
            'options' => 'Assurances',
            'created_at' => now(),
            'updated_at' => now(),
            // 'icon'=>'loop.png',
        ]);

        parametre::firstOrCreate([
            'nom' => 'Rappels à 1 mois',
            'limite' => 30,
            'options' => 'Visites techniques',
            'created_at' => now(),
            'updated_at' => now(),
            // 'icon'=>'loop.png',
        ]);

    }
}
