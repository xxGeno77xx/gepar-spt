<?php

namespace Database\Seeders;

use App\Models\ConsommationCarburant;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsommationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start_timestamp = strtotime('2023-01-01');

        $end_timestamp = strtotime('2023-12-31');

        $previous_timestamp = mt_rand($start_timestamp, $end_timestamp);

        $valeurPrecedente = 0;

        for ($i = 0; $i < 14; $i++) {

            $nouvelleValeur = $this->genererValeur($valeurPrecedente);

            $valeurPrecedente = $nouvelleValeur;

            $next_timestamp = mt_rand($previous_timestamp + 1, $end_timestamp);

            ConsommationCarburant::firstOrCreate([
                'quantite' => mt_rand(1, 200),
                'engine_id' => 1,
                'date_prise' => Carbon::parse($next_timestamp)->format('Y-m-d'),
                'carburant_id' => 1,
                'prix_unitaire' => 700,
                'montant_total' => 700 * mt_rand(1, 200),
                'carte_recharge_id' => mt_rand(1, 200),
                'chauffeur_id' => 1,
                'observation' => 'ok',
                'kilometres_a_remplissage' => $nouvelleValeur,
                'ticket' => mt_rand(1, 200).Str::random(4),
                'state' => StatesClass::Activated()->value,
            ]);

            $previous_timestamp = $next_timestamp;
        }
    }

    public function genererValeur($valeurPrecedente)
    {

        $nouvelleValeur = $valeurPrecedente + rand(142, 198);

        return $nouvelleValeur;
    }
}
