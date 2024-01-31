<?php

namespace Database\Seeders;

use App\Models\Chauffeur;
use Illuminate\Database\Seeder;

class ChauffeurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Chauffeur::create([
            'name' => 'Buckner',
            'prenom' => 'Antoine',
            'departement_id' => 1,
        ]);

        Chauffeur::create([
            'name' => 'Nom_chauffeur2',
            'prenom' => 'Prenom_chauffeur2',
            'departement_id' => 2,
        ]);
    }
}
