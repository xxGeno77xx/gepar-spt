<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Autorisation_speciale()
 * @method static self A1()
 * @method static self B()
 * @method static self C()
 * @method static self D()
 * @method static self E()
 * @method static self F()

 */
class CategoryPermisClass extends Enum

{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'Autorisation_speciale' => 'Autorisation spéciale',
            ];

            return strtr(str_replace('_', ' ', str($name)), $traductions);
        };
    }
}
