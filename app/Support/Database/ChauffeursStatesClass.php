<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self En_mission()
 * @method static self Disponible()
 * @method static self Programme()
 */
class ChauffeursStatesClass extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'En_mission' => 'En mission',
                'Programme' => 'Programmé pour une mission',
            ];

            return strtr(str_replace('_', ' ', str($name)), $traductions);
        };
    }
}
