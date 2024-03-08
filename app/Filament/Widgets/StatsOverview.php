<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\Parametre;
use App\Support\Database\StatesClass;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getCards(): array
    {

        // $visitesASurveiller = Engine::Join('visites', function ($join) {

        //     $join->on('engines.id', '=', 'visites.engine_id')
        //         ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [StatesClass::Activated()->value])
        //         ->whereRaw("DATE(visites.date_expiration)<= DATE_ADD(CURDATE(), INTERVAL  $limiteV DAY) ")
        //         ->where('visites.state', StatesClass::Activated()->value)
        //         ->whereNull('visites.deleted_at');
        // })
        //     ->select('engines.plate_number')
        //     ->where('engines.state', StatesClass::Activated()->value)
        //     ->distinct('engines.id');

        // $assurancesASurveiller = Engine::Join('assurances', function ($join) {

        //     $join->on('engines.id', '=', 'assurances.engine_id')
        //         ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state =?)', [StatesClass::Activated()->value])
        //         ->whereRaw("DATE(assurances.date_fin)<= DATE_ADD(CURDATE(), INTERVAL  $limiteA DAY) ")
        //         ->where('assurances.state', StatesClass::Activated()->value)
        //         ->whereNull('assurances.deleted_at');
        // })
        //     ->select('engines.plate_number')
        //     ->where('engines.state', StatesClass::Activated()->value)
        //     ->distinct('engines.id');

        $activated = StatesClass::Activated()->value;

        $limiteA = parametre::where('options', 'Assurances')->value('limite');

        $limiteV = parametre::where('options', 'Visites techniques')->value('limite');

        $assurancesASurveiller = Engine::Join('assurances', 'engines.id', '=', 'assurances.engine_id')
            ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [$activated])
            ->whereRaw('TRUNC(assurances.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limiteA])
            ->where('assurances.state', $activated)
            ->whereNull('assurances.deleted_at')
            ->whereNull('engines.deleted_at')
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', 'marques.logo as logo', 'assurances.date_debut as date_debut', 'assurances.date_fin as date_fin')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'assurances.date_fin',
                'assurances.date_debut',
                'engines.id',
                'engines.modele_id',
                'engines.power',
                'engines.departement_id',
                'engines.price',
                'engines.circularization_date',
                'engines.date_aquisition',
                'engines.plate_number',
                'engines.type_id',
                'engines.car_document',
                'engines.carburant_id',
                'engines.assurances_mail_sent',
                'engines.visites_mail_sent',
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.carosserie',
                'engines.pl_ass',
                'engines.matricule_precedent',
                'engines.poids_total_en_charge',
                'engines.poids_a_vide',
                'engines.poids_total_roulant',
                'engines.Charge_utile',
                'engines.largeur',
                'engines.surface',
                'engines.couleur',
                'engines.date_cert_precedent',
                'engines.kilometrage_achat',
                'engines.numero_carte_grise',
                'engines.user_id',
                'engines.updated_at_user_id',
                'engines.deleted_at',
                'engines.created_at',
                'engines.updated_at',
                'sigle_centre',
                'nom_modele',
                'nom_marque',
                'logo',
                'remainder'

            )
            ->distinct();

        $visitesASurveiller = Engine::Join('visites', 'engines.id', '=', 'visites.engine_id')
            ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [$activated])
            ->whereRaw('TRUNC(visites.date_expiration) <= TRUNC(SYSDATE + TRUNC(?))', [$limiteV])
            ->where('visites.state', $activated)
            ->whereNull('visites.deleted_at')
            ->whereNull('engines.deleted_at')
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'centre.sigle',*/ 'marques.logo as logo', 'visites.date_initiale as date_initiale', 'visites.date_expiration as date_expiration')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'visites.date_expiration',
                'visites.date_initiale',
                'engines.id',
                'engines.modele_id',
                'engines.power',
                'engines.departement_id',
                'engines.price',
                'engines.circularization_date',
                'engines.date_aquisition',
                'engines.plate_number',
                'engines.type_id',
                'engines.car_document',
                'engines.carburant_id',
                'engines.assurances_mail_sent',
                'engines.visites_mail_sent',
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.carosserie',
                'engines.pl_ass',
                'engines.matricule_precedent',
                'engines.poids_total_en_charge',
                'engines.poids_a_vide',
                'engines.poids_total_roulant',
                'engines.Charge_utile',
                'engines.largeur',
                'engines.surface',
                'engines.couleur',
                'engines.date_cert_precedent',
                'engines.kilometrage_achat',
                'engines.numero_carte_grise',
                'engines.user_id',
                'engines.updated_at_user_id',
                'engines.deleted_at',
                'engines.created_at',
                'engines.updated_at',
                'sigle_centre',
                'nom_modele',
                'nom_marque',
                'logo',
                'remainder'

            )
            ->distinct();

        $enginesCloseToExpiry = $visitesASurveiller->union($assurancesASurveiller)->distinct()->count();

        return [
            Card::make('Total des engins du parc', Engine::where('engines.state', '<>', StatesClass::Deactivated()->value)->count()) //  to do:  where activated  or reparing
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('success'),

            Card::make('Engins à surveiller', $enginesCloseToExpiry)
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('danger'),

        ];
    }
}
