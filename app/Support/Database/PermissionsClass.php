<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/** //=================================================

 * //==================================================
 * ENGINES
 * @method static self Engines_create()
 * @method static self Engines_read()
 * @method static self Engines_update()
 * @method static self Engines_delete()
 * //==================================================
 * REPARATIONS
 * @method static self Reparation_create()
 * @method static self Reparation_read()
 * @method static self Reparation_update()
 * @method static self Reparation_delete()
 * // =================================================
 * DEPARTEMENTS
 * @method static self Departements_create()
 * @method static self Departements_read()
 * @method static self Departements_update()
 * @method static self Departements_delete()
 * // =================================================
 * MARQUES  
 * @method static self Marques_create()
 * @method static self Marques_read()
 * @method static self Marques_update()
 * @method static self Marques_delete()
 * // =================================================
 * MODELES
 * @method static self Modeles_create()
 * @method static self Modeles_read()
 * @method static self Modeles_update()
 * @method static self Modeles_delete()
 * // =================================================
 * VISITES
 * @method static self Visites_create()
 * @method static self Visites_read()
 * @method static self Visites_update()
 * @method static self Visites_delete()
 * // =================================================
 * ASSURANCES
 * @method static self Assurances_create()
 * @method static self Assurances_read()
 * @method static self Assurances_update()
 * @method static self Assurances_delete()
 * // =================================================
 * TYPES
 * @method static self Types_create()
 * @method static self Types_read()
 * @method static self Types_update()
 * @method static self Types_delete()
 * // =================================================
 * PARAMETRES
 * @method static self Parametre_read()
 * @method static self Parametre_update()
 * // =================================================
 * USERS
 * @method static self Users_create()
 * @method static self Users_read()
 * @method static self Users_update()
 * @method static self Users_delete()
 * // =================================================
 * PERMISSIONS
 * @method static self Permissions_read()
 * // =================================================
 * 
 * TYPES REPARATION
 * @method static self TypesReparations_manage()
 * // =================================================
 * USERS
 * @method static self Roles_create()
 * @method static self Roles_read()
 * @method static self Roles_update()
 * @method static self Roles_delete()
 * //=================================================
 * CHAUFFEURS
 * @method static self Chauffeurs_create()
 * @method static self Chauffeurs_read()
 * @method static self Chauffeurs_update()
 * @method static self Chauffeurs_delete()
 * // =================================================
 */

class PermissionsClass extends Enum
{

    protected static function values()
    {
        return function(string $name): string|int {

            $traductions = array(
                "Engines" => "Engins",
                "create" => "ajouter",
                "read" => "voir",
                "update" => "modifier",
                "delete" => "supprimer",
                "Parametre" => "Paramètre",
                "Modeles" => "Modèles",
                "Departements" => "Départements",
                "Reparation" => "Réparation",
                "Users"=>"Utilisateurs"
            );
            return strtr(str_replace("_", ": ", str($name)), $traductions);;
        };
    }
}