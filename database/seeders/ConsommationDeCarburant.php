<?php

namespace Database\Seeders;

use App\Models\ConsommationCarburant;
use Illuminate\Database\Seeder;

class ConsommationDeCarburant extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $int = mt_rand(1262055681, 1262055681);

            ConsommationCarburant::firstOrCreate([
                'quantite' => mt_rand(1, 200),
                'engine_id' => 1,
                'date' => $string = date('Y-m-d H:i:s', $int),
                'carburant_id' => 1,
                'carte_recharge_id' => mt_rand(1, 200),
                'chauffeur_id' => 1,
                'observation' => 'ok',
                'kilometres_a_remplissage' => 200,
                'ticket' => mt_rand(1, 200).'dnmlj',
            ]);
        }
    }
}
