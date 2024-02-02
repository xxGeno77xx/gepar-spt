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
            "age" => 22,
            "carte_identite"=> "cni1",
            "num_permis"=> "123456789"
        ]);

        Chauffeur::create([
            'name' => 'Nom_chauffeur2',
            'prenom' => 'Prenom_chauffeur2',
                "age" => 22,
                "carte_identite"=> "cni2", 
                "num_permis"  => "123456789" 
        ]);

        Chauffeur::create([
            'name' => 'Nom_chauffeur3',
            'prenom' => 'Prenom_chauffeur3',
                "age" => 55,
                "carte_identite"=> "cni25", 
                "num_permis"  => "123456712289" 
        ]);
    }
}
