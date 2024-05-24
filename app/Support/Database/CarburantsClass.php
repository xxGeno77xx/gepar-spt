<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Diesel()
 * @method static self Essence()
 * @method static self Gaz_naturel()
 * @method static self GPL()
 * @method static self Hybride()
 * @method static self Electrique()
 * @method static self Biocarburant()
 */
class CarburantsClass extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'Gaz_naturel' => 'Gaz naturel',
            ];

            return strtr(str_replace('_', ' ', str($name)), $traductions);
        };
    }
}
