<?php

namespace Database\Seeders;

use App\Models\Prestataire;
use App\Support\Database\PrestatairesClass;
use Illuminate\Database\Seeder;

class PrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prestataires = PrestatairesClass::toValues();

        foreach ($prestataires as $key => $prestataire) {
            Prestataire::firstOrCreate([
                'nom' => $prestataire,
            ]);
        }
    }
}
