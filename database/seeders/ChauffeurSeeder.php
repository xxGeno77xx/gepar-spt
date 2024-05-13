<?php

namespace Database\Seeders;

use App\Models\Permis;
use App\Models\Chauffeur;
use Illuminate\Database\Seeder;
use App\Support\Database\CategoryPermisClass;
use App\Support\Database\ChauffeursStatesClass;

class ChauffeurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $chauffeursNames = [
            'KOUYENE Abalo Dovene',
            'PATABOU Ndjandema Kouberabalo',
            'NADIO Amadou',
            'MOROU Abdel-Rachidou',
            'GALLEY Komla',
            'AFFO Kidom Alaï',
            'NAPO Waké',
            'BASSA Kodjovi',
            'GUENOU Yao',
            'DANHOUI Kossi',
            'TOSSIM Essodom',
        ];


        $categoriesPermis =[
            CategoryPermisClass::Autorisation_speciale()->value,
            CategoryPermisClass::A1()->value,
            CategoryPermisClass::B()->value,
            CategoryPermisClass::C()->value,
            CategoryPermisClass::D()->value,
            CategoryPermisClass::E()->value,
            CategoryPermisClass::F()->value,
        ];

        foreach ($chauffeursNames as $name) {
            Chauffeur::create([
                'fullname' => $name,
                'mission_state' => ChauffeursStatesClass::Disponible()->value,
            ]);
        }

        foreach($categoriesPermis as $permis)
        {
            Permis::create([
                "libelle" => $permis
            ]);
        }
       

    }
}
