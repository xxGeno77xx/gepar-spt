<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Transport_a_deux_roues()
 * @method static self Tricycle_motorises()
 * @method static self Bus()
 * @method static self Cargo()
 * @method static self Fourgonette()
 * @method static self Camion()
 * @method static self Camionnette()
 */
class TypesClass extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [

                'Transport_a_deux_roues' => 'Moto',
                'Tricycle_motorises' => 'Tricycle motorisés',
            ];

            return strtr(str_replace('_', ' ', str($name)), $traductions);
        };
    }
}
