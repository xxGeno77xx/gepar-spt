<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Engine;
use App\Models\Marque;
use App\Models\Modele;
use App\Models\Visite;
use App\Models\Assurance;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Support\Database\StatesClass;
use App\Support\Database\EnginesClass;


class EnginesSeeder extends Seeder
{
   /**
    * Run the database seeds.
    */
   public function run(): void
   {
      $plates = [
         "1236-AF",
         "1364-AC",
         "1995-AS",
         "1242-AX",
         "1111-AG",
         "0003-AZ",
         "3000-AR",
         "1036-AB"
      ];

      foreach ($plates as $key => $plate) {
         Engine::firstOrCreate([
            'modele_id' => mt_rand(1, 21),
            'power' => mt_rand(1, 100),
            'plate_number' => $plate,
            'type_id' => mt_rand(1, 5),
            'carburant_id' => mt_rand(1, 2),
            'state' => StatesClass::Activated(),
            'user_id' => 1,
            'updated_at_user_id' => 1,
            "assurances_mail_sent" => 0,
            "visites_mail_sent" => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'carosserie' => 'Bâchée',
            'pl_ass' => 04,
            'poids_total_en_charge' => 955,
            "poids_a_vide" => 950,
            "poids_total_roulant" => null,
            "Charge_utile" => 200,
            'largeur' => 120,
            'surface' => 123,
            'couleur' => '#852a2a',
            'numero_chassis' => Str::random(4),
            'moteur' => 05,
            'kilometrage_achat' => 25000,
            "departement_id" =>mt_rand(1,5)
         ]);

         Visite::create([
            "engine_id" => $key + 1,
            'date_initiale' => Carbon::now()->subDays(rand(1, 50)),
            'date_expiration' => Carbon::now()->addYear(),
            'user_id' => 1,
            'updated_at_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
         ]);

         Assurance::create([
            "engine_id" => $key + 1,
            'date_debut' => Carbon::now()->subDays(rand(1, 50)),
            'date_fin' => Carbon::now()->addMonths(rand(1, 12)),
            'user_id' => 1,
            'updated_at_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }

      Visite::create([
         "engine_id" => $key + 1,
         'date_initiale' => Carbon::now()->subYear(),
         'date_expiration' => Carbon::now()->addDays(3),
         'user_id' => 1,
         'updated_at_user_id' => 1,
         'created_at' => now(),
         'updated_at' => now(),
      ]);

      Assurance::create([
         "engine_id" => $key + 1,
         'date_debut' => Carbon::now()->subYear(),
         'date_fin' => Carbon::now()->addDays(3),
         'user_id' => 1,
         'updated_at_user_id' => 1,
         'created_at' => now(),
         'updated_at' => now(),
      ]);
   }


}




