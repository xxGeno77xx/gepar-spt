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
 * @method static self Delegue_Direction()
 * @method static self Delegue_Division()
 * @method static self Delegue_Direction_Generale()
 * @method static self Interimaire_DG()
 * @method static self Interimaire_Directeur()
 * @method static self Interimaire_Chef_division()
 * @method static self Interimaire_Chef_parc()
 * @method static self Chef_section()
 */
class RolesEnum extends Enum
{
    protected static function values()
    {

        return [
            'Super_administrateur' => 'Super administrateur',
            'Chef_division' => 'Chef division',
            'Chef_parc' => 'Chef parc ',
            'Directeur_general' => 'Directeur général',
            // 'Secretaire' => 'Sécrétaire',
            'Delegue_Division' => 'Délégué de division',
            'Delegue_Direction' => 'Délégué de direction',
            'Delegue_Direction_Generale' => 'Délégué de direction générale',
            'Interimaire_DG' => 'Intérimaire du Directeur général',
            'Interimaire_Directeur' => 'Intérimaire du Directeur',
            'Interimaire_Chef_division' => 'Intérimaire du Chef Division',
            'Interimaire_Chef_parc' => 'Intérimaire du Chef Parc',
            'Chef_section' => 'Chef section',
        ];

    }
}
