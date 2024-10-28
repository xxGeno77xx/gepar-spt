<?php

namespace App\Functions;

use App\Models\Circuit;
use App\Models\Engine;
use App\Models\Role;
use App\Models\Type;
use App\Support\Database\TypesClass;
use Illuminate\Database\Eloquent\Model;

class ControlFunctions
{
    public static function checkEngineType($engineId)
    {
        $engineType = Engine::find($engineId)->type_id;

        $categoriesWithoutValidationIds = Type::whereIn('nom_type', [
            TypesClass::Transport_a_deux_roues()->value,
            TypesClass::Tricycle_motorises()->value,
        ])->pluck('id')->toArray();

        return in_array($engineType, $categoriesWithoutValidationIds);
    }

    public static function getNthOccurrenceOfRequiredRole(Model $record, string $role, int $n)
    {

        $circuit = Circuit::find($record->circuit_id)?->steps ?? null;

        if ($circuit) {
            foreach ($circuit as $key => $item) {

                $array[] = $item['role_id'];
            }

            $value = Role::where('name', $role)->first()->id;

            $occurrenceCount = 0;

            foreach ($array as $key => $item) {
                if ($item === $value) {
                    $occurrenceCount++;
                    if ($occurrenceCount === $n) {
                        return $key;
                    }
                }
            }
        }

        return null;
    }

    public static function getIndicesAfterNthOccurrence(Model $record, $value, int $n)
    {
        $nthOccurrenceIndex = self::getNthOccurrenceOfRequiredRole($record, $value, $n);

        $circuit = Circuit::find($record->circuit_id)?->steps ?? null;

        if ($circuit) {

            foreach ($circuit as $key => $item) {

                $array[] = $item['role_id'];
            }

            if ($nthOccurrenceIndex === -1) {
                return []; // Retourner un tableau vide si l'occurrence n'est pas trouvée
            }

            $returnArray = array_slice(array_keys($array), $nthOccurrenceIndex);

            array_push($returnArray, 100);

            // Retourner les indices restants après la n-ième occurrence (indice de la nième occurence inclus)
            return $returnArray;
        }

        return [];
    }

    // public static function sendErrorNotificationOnMail($record)
    // {

    // }
}
