<?php

namespace App\Static;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use PDO;

class StoredFunctions
{
    public static function soldeCompteCharge($numero_compte): int
    {
        try {

            $disponibilite = DB::executeFunction('gepar.dispo_charge_budget', [
                'P_RADICAL' => $numero_compte,
                'date_debut' => '2024/01/01',
                'date_fin' => '2024/09/14',
            ], PDO::PARAM_INT);

        } catch (\Exception $e) {
            Notification::make('error')
                ->title('Erreur fonction Oracle')
                ->body('Erreur lors du calcul de disponibilité:'.$e->getMessage())
                ->send();
        }

        return $disponibilite;
    }
}
