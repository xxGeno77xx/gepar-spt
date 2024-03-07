<?php

namespace Database\Seeders;

use App\Models\Circuit;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CircuitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $circuitLong = [
            ["role_id" => 5],
            ["role_id" => 7],
            ["role_id" => 9],
            ["role_id" => 6],
            ["role_id" => 13],
            ["role_id" => 6],
            ["role_id" => 5],
            ["role_id" => 8],
            ["role_id" => 7],
            ["role_id" => 9],
            ["role_id" => 6]
        ];



        // $circuitMoyen =[

        // ];

        // $circuitCourt =[

        // ];

        // $circuitSpecialise =[

        // ];

        Circuit::create([
            "name"=> "Circuit long",
            "steps"=>  $circuitLong,
        ]);

        // Circuit::create([
        //     "name"=> "Circuit moyen",
        //     "steps"=>  $circuitMoyen,
        // ]);

        // Circuit::create([
        //     "name"=> "Circuit court",
        //     "steps"=>  $circuitCourt,
        // ]);

        // Circuit::create([
        //     "name"=> "Circuit specialise",
        //     "steps"=>  $circuitSpecialise,
        // ]);
    }
}
