<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Super_administrateur()
 * @method static self Dpas()
 * @method static self Chef_division()
 * @method static self Chef_parc()
 * @method static self Directeur()
 * @method static self Budget()
 * @method static self Directeur_general()
 * @method static self Dpl()
 * @method static self Secretaire()
 * @method static self Secretaire_DG()
 * @method static self Diga()
 */
class RolesEnum extends Enum
{

    protected static function values()
    {
         
        return [
            'Super_administrateur' => "Super administrateur",
            'Chef_division' => "Chef division",
            'Chef_parc' =>  "Chef parc ",
            'Directeur_general' =>  "Directeur général",
            "Secretaire" => "Sécrétaire",
        
        ];

          
    }
}
