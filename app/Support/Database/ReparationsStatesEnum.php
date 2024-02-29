<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Rejected()
 * @method static self Pending()
 * @method static self Validated()
 */
class ReparationsStatesEnum extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'Rejected' => 'Rejetée',
                'Pending' => 'En attente de validation',
                'Validated' => 'Validée',
            ];

            return strtr(str_replace('_', ': ', str($name)), $traductions);
        };
    }
}
