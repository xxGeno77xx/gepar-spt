<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Database\PermissionsClass;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const SuperAdmin = 'Super administrateur';

    const Manager = 'Manager';

    const User = 'Utilisateur';

    public function run(): void
    {

        $permissions = PermissionsClass::toValues();

        foreach ($permissions as $key => $name) {
            Permission::firstOrCreate([
                'name' => $name,
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => RolesEnum::Super_administrateur()->value,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        $superAdmin = User::firstOrCreate([
            'email' => 'gepar@laposte.tg', //  myself
            'password' => Hash::make('L@poste+2024'),
            'name' => 'gepar',
            'username' => 'gepar',
            'notification' => true,
            'login_attempts' => 0,
            // 'departement_id' => 1,  //division_id
            'created_at' => now(),
            'updated_at' => now(),
            'state' => StatesClass::Activated()->value,

        ]);

        $superAdmin->syncRoles(RolesEnum::Super_administrateur()->value);

        //=============Manager================

        $parameters = [
            PermissionsClass::Parametre_read()->value,
            PermissionsClass::Parametre_update()->value,
        ];

        $ManagerRole = Role::firstOrCreate([
            'name' => self::Manager,
            'guard_name' => 'web',
        ]);

        $ManagerRole->syncPermissions($parameters);

        //========users==============

        $userRole = Role::firstOrCreate([
            'name' => self::User,
            'guard_name' => 'web',
        ]);

        $usersPermissions = Arr::where($permissions, function ($value) {

            return !in_array($value, [
                PermissionsClass::Parametre_read()->value,
                PermissionsClass::Parametre_update()->value,
                PermissionsClass::Permissions_read()->value,

                PermissionsClass::Roles_create()->value,
                PermissionsClass::Roles_read()->value,
                PermissionsClass::Roles_update()->value,
                PermissionsClass::Roles_delete()->value,
                PermissionsClass::Roles_create()->value,

                PermissionsClass::users_create()->value,
                PermissionsClass::users_read()->value,
                PermissionsClass::users_update()->value,
                PermissionsClass::users_delete()->value,

                PermissionsClass::Chauffeurs_create()->value,
                PermissionsClass::Chauffeurs_read()->value,
                PermissionsClass::Chauffeurs_update()->value,

                PermissionsClass::Assurances_create()->value,
                PermissionsClass::Assurances_read()->value,
                PermissionsClass::Assurances_update()->value,

                PermissionsClass::Visites_create()->value,
                PermissionsClass::Visites_read()->value,
                PermissionsClass::Visites_update()->value,

                PermissionsClass::Carburant_create()->value,
                PermissionsClass::Carburant_read()->value,
                PermissionsClass::Carburant_update()->value,

                PermissionsClass::Carburant_create()->value,
                PermissionsClass::Carburant_read()->value,
                PermissionsClass::Carburant_update()->value,

                PermissionsClass::TypesReparations_manage()->value,

                PermissionsClass::marques_read()->value,
                PermissionsClass::marques_update()->value,
                PermissionsClass::Marques_create()->value,
                PermissionsClass::Marques_delete()->value,

                PermissionsClass::modeles_read()->value,
                PermissionsClass::modeles_update()->value,
                PermissionsClass::Modeles_create()->value,
                PermissionsClass::Modeles_delete()->value,

                PermissionsClass::Types_create()->value,
                PermissionsClass::Types_read()->value,
                PermissionsClass::Types_update()->value,
            ]);
        });

        $userRole->syncPermissions($usersPermissions);


        $roles = RolesEnum::toValues();

        foreach ($roles as $key => $role) {
            Role::firstOrCreate([
                'name' => $role,
            ]);
        }

        ////////////DPL permissions ///////

        $DdplPermissions = [

            PermissionsClass::Chauffeurs_create()->value,
            PermissionsClass::Chauffeurs_read()->value,
            PermissionsClass::Chauffeurs_update()->value,

            PermissionsClass::Assurances_create()->value,
            PermissionsClass::Assurances_read()->value,
            PermissionsClass::Assurances_update()->value,

            PermissionsClass::Visites_create()->value,
            PermissionsClass::Visites_read()->value,
            PermissionsClass::Visites_update()->value,

            PermissionsClass::Carburant_create()->value,
            PermissionsClass::Carburant_read()->value,
            PermissionsClass::Carburant_update()->value,

            PermissionsClass::Carburant_create()->value,
            PermissionsClass::Carburant_read()->value,
            PermissionsClass::Carburant_update()->value,

            PermissionsClass::Engines_create()->value,
            PermissionsClass::Engines_read()->value,
            PermissionsClass::Engines_update()->value,

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::TypesReparations_manage()->value,

            PermissionsClass::Types_create()->value,
            PermissionsClass::Types_read()->value,
            PermissionsClass::Types_update()->value,

            PermissionsClass::marques_read()->value,
            PermissionsClass::marques_update()->value,
            PermissionsClass::Marques_create()->value,
            PermissionsClass::Marques_delete()->value,

            PermissionsClass::modeles_read()->value,
            PermissionsClass::modeles_update()->value,
            PermissionsClass::Modeles_create()->value,
            PermissionsClass::Modeles_delete()->value,

        ];

        (Role::where('name', RolesEnum::Dpl()->value))->first()->syncPermissions($DdplPermissions);

        //Directeurs permissions

        $directeursPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Directeur()->value))->first()->syncPermissions($directeursPermissions);


        (Role::where('name', RolesEnum::Drhp()->value))->first()->syncPermissions($directeursPermissions);
        //BUDGET permissions

        $budgetPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Budget()->value))->first()->syncPermissions($budgetPermissions);

        (Role::where('name', RolesEnum::Chef_dcgbt()->value))->first()->syncPermissions($budgetPermissions);

        //DPAS permissions

        $dPasPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Dpas()->value))->first()->syncPermissions($dPasPermissions);

        //CHEF DIVISIONS Permissions

        $chefsDivisionsPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Chef_division()->value))->first()->syncPermissions($chefsDivisionsPermissions);

        //DG  Permissions

        $dGPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Directeur_general()->value))->first()->syncPermissions($dGPermissions);

        //DIGA  Permissions

        $diGAPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Diga()->value))->first()->syncPermissions($diGAPermissions);

        //Delegues division Permissions

        $deleguesDivisionsPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Delegue_Division()->value))->first()->syncPermissions($deleguesDivisionsPermissions);

        //Delegues direction Permissions

        $deleguesDirectionPermissions = [

            PermissionsClass::Reparation_create()->value,
            PermissionsClass::Reparation_read()->value,
            PermissionsClass::Reparation_update()->value,

            PermissionsClass::Engines_read()->value,
        ];

        (Role::where('name', RolesEnum::Delegue_Direction()->value))->first()->syncPermissions($deleguesDirectionPermissions);

    }
}
