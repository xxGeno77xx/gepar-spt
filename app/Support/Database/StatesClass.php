<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Activated()
 * @method static self Deactivated()
 * @method static self Suspended()
 * @method static self Working()
 * @method static self Repairing()
 */
class StatesClass extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'Activated' => 'En état',
                'Deactivated' => 'Désactivé',
                'Repairing' => 'En réparation',
            ];

            return strtr(str_replace('_', ': ', str($name)), $traductions);
        };
    }
}
