<?php

namespace Database\Seeders;

use App\Models\Prestataire;
use Illuminate\Database\Seeder;
use App\Support\Database\PrestatairesClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PrestataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prestataires = PrestatairesClass::toValues();

        foreach( $prestataires as $key => $prestataire)
        {
            Prestataire::firstOrCreate([
                'nom' =>  $prestataire,
            ]);
        }
    }
}
