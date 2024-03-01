<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Declaration_initiale()
 * @method static self Demande_de_travail_Chef_division()
 * @method static self Demande_de_travail_directeur_division()
 * @method static self Demande_de_travail_dg()
 * @method static self Demande_de_travail_diga()
 * @method static self Demande_de_travail_chef_parc()
 * @method static self Rejete()
 */
class ReparationValidationStates extends Enum
{
    protected static function values()
    {
         
        return [
            'Declaration_initiale' => "Demande initiale",
            'Demande_de_travail_Chef_division' => "Validée par le chef Division",
            'Demande_de_travail_directeur_division' =>  "Validée par le Directeur ",
            'Demande_de_travail_dg' =>  "Validée par le DG",
            'Demande_de_travail_chef_parc' =>  "Validée par le chef Parc",
            'Demande_de_travail_diga' =>  "Validée par la DIGA",
            
        ];

          
    }
}
