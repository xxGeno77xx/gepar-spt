<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\DepartementUser;
use Illuminate\Database\Seeder;
use App\Support\Database\RolesEnum;
use Illuminate\Support\Facades\Hash;
use App\Support\Database\StatesClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DirecteursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directeurs = collect(
            [
                [
                    //DCR
                    'name' => 'Thiery ADZOMADA',
                    'username' => 'ADZOMADAT',
                    'email' => Str::random(12).'@laposte.tg',
                    'notification' => 1,
                    'password' => Hash::make('L@poste+2024'),
                    'login_attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'state' => StatesClass::Activated()->value,
                    "departement_id" => 3,
                ],

                [
                    //DRHP
                    'name' => 'TCHESSOTAGBA Pidénam',
                    'username' => 'TCHESSOTAGBA',
                    'email' => Str::random(12).'@laposte.tg',
                    'notification' => 1,
                    'password' => Hash::make('L@poste+2024'),
                    'login_attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'state' => StatesClass::Activated()->value,
                    "departement_id" => 23,
                ],
            ]
        );

        foreach ($directeurs as $directeur) {
            $createdDirecteur = User::firstOrCreate($directeur);
            $createdDirecteur->syncRoles(RolesEnum::Directeur()->value);
        }


        $dcrID = 7 ;
        $drhpID = 8;

        $dcrCentres = collect(
            [

                //DCR
                ["departement_code_centre" => 3, "user_id" => $dcrID], //DCR
                ["departement_code_centre" => 650, "user_id" => $dcrID], // EMS
                ["departement_code_centre" => 20, "user_id" => $dcrID], //DAT
                ["departement_code_centre" => 32, "user_id" => $dcrID],  //DCE


                //DRHP
                ["departement_code_centre" => 23, "user_id" => $drhpID], //DRHP
                ["departement_code_centre" => 6, "user_id" => $drhpID], // DPAS
                ["departement_code_centre" => 730, "user_id" => $drhpID], // DPL
            ]
        );

        foreach ($dcrCentres as $centre) {
            DepartementUser::firstOrCreate($centre);
        }

    }
}
