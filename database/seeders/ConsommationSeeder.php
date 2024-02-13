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

        for ($i = 0; $i < 14; $i++) {

            $previous_number = mt_rand(12620, 20000);

            $next_number = mt_rand($previous_number + 1, $previous_number + 100);

            $next_timestamp = mt_rand($previous_timestamp + 1, $end_timestamp);

            ConsommationCarburant::firstOrCreate([
                'quantite' => mt_rand(1, 200),
                'engine_id' => 1,
                'date_prise' => Carbon::parse($next_timestamp)->format('Y-m-d'),
                'carburant_id' => 1,
                'carte_recharge_id' => mt_rand(1, 200),
                'chauffeur_id' => 1,
                'observation' => 'ok',
                'kilometres_a_remplissage' => $next_number,
                'ticket' => mt_rand(1, 200).Str::random(4),
                'state' => StatesClass::Activated()->value,
            ]);

            $previous_timestamp = $next_timestamp;
            $previous_number = $next_number;
        }
    }
}
