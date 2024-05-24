<?php

namespace Database\Seeders;

use App\Models\Assurance;
use App\Models\Engine;
use App\Models\Visite;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnginesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plates = [
            'TG 1236-AF',
            'TG 1364-AC',
            'TG 1995-AS',
            'TG 1242-AX',
            'TG 1111-AG',
            'TG 0003-AZ',
            'TG 3000-AR',
            'TG 1036-AB',
            'TG 2468-AF',
        ];

        foreach ($plates as $key => $plate) {
            Engine::firstOrCreate([
                'modele_id' => mt_rand(1, 21),
                'power' => mt_rand(1, 100),
                'plate_number' => $plate,
                'type_id' => mt_rand(1, 2),
                'carburant_id' => mt_rand(1, 2),
                'state' => StatesClass::Activated()->value,
                'user_id' => 1,
                'updated_at_user_id' => 1,
                'assurances_mail_sent' => 0,
                'visites_mail_sent' => 0,
                'tvm_mail_sent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'carosserie' => 'Bâchée',
                'pl_ass' => 04,
                'poids_total_en_charge' => 955,
                'poids_a_vide' => 950,
                'poids_total_roulant' => null,
                'charge_utile' => 200,
                'largeur' => 120,
                'surface' => 123,
                'couleur' => '#852a2a',
                'numero_chassis' => Str::random(4),
                'moteur' => 05,
                'kilometrage_achat' => 25000,
                'departement_id' => mt_rand(1, 5),
                'numero_carte_grise' => Str::random(6),
                'date_aquisition' => today(),
            ]);

            Visite::create([
                'engine_id' => $key + 1,
                'date_initiale' => Carbon::now()->subDays(rand(1, 50)),
                'date_expiration' => Carbon::now()->addYear(),
                'user_id' => 1,
                'updated_at_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'state' => StatesClass::Activated()->value,
            ]);

            Assurance::create([
                'engine_id' => $key + 1,
                'date_debut' => Carbon::now()->subDays(rand(1, 50)),
                'date_fin' => Carbon::now()->addMonths(rand(1, 12)),
                'assureur_id' => 9,
                'user_id' => 1,
                'updated_at_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'state' => StatesClass::Activated()->value,
            ]);
        }

        Visite::create([
            'engine_id' => $key + 1,
            'date_initiale' => Carbon::now()->subYear(),
            'date_expiration' => Carbon::now()->addDays(3),
            'user_id' => 1,
            'updated_at_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'state' => StatesClass::Activated()->value,
        ]);

        Assurance::create([
            'engine_id' => $key + 1,
            'date_debut' => Carbon::now()->subYear(),
            'date_fin' => Carbon::now()->addDays(3),
            'user_id' => 1,
            'assureur_id' => 9,
            'updated_at_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'state' => StatesClass::Activated()->value,
        ]);
    }
}
