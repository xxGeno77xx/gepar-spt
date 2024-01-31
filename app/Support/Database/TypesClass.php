<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Bus()
 * @method static self Bus_15_places()
 * @method static self Bus_32_places()
 * @method static self Citadine()
 * @method static self Pickup()
 * @method static self Fourgon()
 * @method static self Camionnette()
 * @method static self Moto()
 * @method static self Tricycle()
 */
class TypesClass extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [];

            return strtr(str_replace('_', ' ', str($name)), $traductions);
        };
    }
}
