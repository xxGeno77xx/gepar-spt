<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Support\Database\StatesClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChefsDivisionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $directeurs = collect(
        //     [
        //         [
        //             //DCR
        //             'name' => 'Thiery ADZOMADA',
        //             'username' => 'ADZOMADAT',
        //             'email' => Str::random(12).'@laposte.tg',
        //             'notification' => 1,
        //             'password' => Hash::make('L@poste+2024'),
        //             'login_attempts' => 0,
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //             'state' => StatesClass::Activated()->value,
        //             "departement_id" => 3,
        //         ],

        //     ]);

    }
}
