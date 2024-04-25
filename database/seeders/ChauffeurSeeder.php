<?php

namespace Database\Seeders;

use App\Models\Chauffeur;
use App\Support\Database\ChauffeursStatesClass;
use Illuminate\Database\Seeder;

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

        foreach ($chauffeursNames as $name) {
            Chauffeur::create([
                'fullname' => $name,
                // 'engine_id' => mt_rand(1, 7),
                'mission_state' => ChauffeursStatesClass::Disponible()->value,
            ]);
        }

    }
}
