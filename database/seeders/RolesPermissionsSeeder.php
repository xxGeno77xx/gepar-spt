<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Support\Database\PermissionsClass;


class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const SuperAdmin='Super administrateur';

    const Manager='Manager';

    const User='Utilisateur';

    public function run(): void
    {

        $permissions = PermissionsClass::toValues();

        foreach ($permissions as $key => $name) {
            Permission::firstOrCreate([
                'name' => $name,
            ]);
        }

        $role=Role::firstOrCreate([
            'name'=>self::SuperAdmin,
            'guard_name'=>'web',
        ]);

        $role->syncPermissions($permissions);

        $superAdmin=User::firstOrCreate([
            "email"=>"superadministrateur@laposte.tg", //  myself
            'password'=> Hash::make('11111111'),
            'name'=>'Super_administrateur',
            'notification'=>true,
            
        ]);

        $superAdmin->syncRoles(self::SuperAdmin);

        //=============Manager================ 

        $parameters = [
            PermissionsClass::Parametre_read()->value,
            PermissionsClass::Parametre_update()->value,
        ];

        $ManagerRole = Role::firstOrCreate([
            'name'=>self::Manager,
            'guard_name'=>'web',
        ]);

        $ManagerRole->syncPermissions($parameters);
        


        //========users==============

        $userRole = Role::firstOrCreate([
            'name'=>self::User,
            'guard_name'=>'web',
        ]);


        $usersPermissions =[
            
        ];

        $sptUsers = collect([
            ["name" =>"DJAGBANI Paguedame", "email" => "Paguedame.Djagbani@laposte.tg", "notification" => 1, "password" => Hash::make("L@poste+2024"), "login_attempts" => 0, "created_at" => now(), "updated_at" => now()],
            ["name" =>"TCHOYO Yaou", "email" => "Yaou.Tchoyo@laposte.tg", "notification" => 1, "password" => Hash::make("L@poste+2024"), "login_attempts" => 0, "created_at" => now(), "updated_at" => now()],
            ["name" =>"KOMBATE Arzouma  ", "email" => "Arzouma.Kombate@laposte.tg", "notification" => 1, "password" => Hash::make("L@poste+2024"), "login_attempts" => 0, "created_at" => now(), "updated_at" => now()],
            ["name" =>"TCHESSOTAGBA Pidénam", "email" => "Pidename.Tchessotagba@laposte.tg", "notification" => 1, "password" => Hash::make("L@poste+2024"), "login_attempts" => 0, "created_at" => now(), "updated_at" => now()],
        ]);

        foreach($sptUsers as $user){
            $createdUser = User::firstOrCreate($user);
            $createdUser->syncRoles(self::User);
        }
    }
}
