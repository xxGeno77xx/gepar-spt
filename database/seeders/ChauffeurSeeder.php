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
            'name' => 'Chauffeur_1',
            'prenom' => 'Antoine',
            'age' => 22,
            'carte_identite' => 'cni1',
            'num_permis' => '123456789',
            'permmis' => '/',
        ]);

        Chauffeur::create([
            'name' => 'Chauffeur_2',
            'prenom' => 'Prenom_chauffeur2',
            'age' => 22,
            'carte_identite' => 'cni2',
            'num_permis' => '123456789',
            'permmis' => '/',

        ]);

        Chauffeur::create([
            'name' => 'Chauffeur_3',
            'prenom' => 'Prenom_chauffeur3',
            'age' => 55,
            'carte_identite' => 'cni25',
            'num_permis' => '123456712289',
            'permmis' => '/',
        ]);
    }
}
