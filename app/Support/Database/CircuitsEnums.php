<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self circuit_de_division()
 * @method static self circuit_de_direction()
 * @method static self circuit_de_la_direction_generale()
 * @method static self circuit_particulier()
 *                                           ==========================================
 * @method static self circuit_de_division_diga_dir()
 * @method static self circuit_de_division_diga_dg()
 *                                                   ==========================================
 * @method static self circuit_de_direction_diga_dir()
 * @method static self circuit_de_direction_diga_dg()
 *                                                    =============================================
 * @method static self circuit_de_la_direction_generale_diga()
 *                                                             =============================================
 * @method static self circuit_particulier_diga()
 */
class CircuitsEnums extends Enum
{
    protected static function values()
    {

        return [
            'circuit_de_division' => 'Circuit de Division',
            'circuit_de_direction' => 'Circuit de Direction',
            'circuit_de_la_direction_generale' => 'Circuit de la Direction Générale',
            'circuit_particulier' => 'Circuit particulier',
            'circuit_de_division_diga_dir' => 'Circuit de division avec avis de la DIGA (transferée par le directeur)',
            'circuit_de_division_diga_dg' => 'Circuit de division avec avis de la DIGA (transferée par le DG)',
            'circuit_de_direction_diga_dir' => 'Circuit de Direction avec avis de la DIGA ( transférée par le directeur)',
            'circuit_de_direction_diga_dg' => 'Circuit de Direction avec avis de la DIGA ( transférée par le DG)',
            'circuit_de_la_direction_generale_diga' => 'Circuit de la Direction Générale avec avis de la DIGA',
            'circuit_particulier_diga' => 'Circuit particulier avec avis de la DIGA',
        ];

    }
}
