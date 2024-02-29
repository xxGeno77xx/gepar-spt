<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Vidange()
 *                               =====================================================
 *                               CHANGEMENT DE PIECES
 * @method static self Changement_de_filtreHuile()
 * @method static self Changement_de_filtreCarburant()
 * @method static self Changement_de_filtreAir()
 * @method static self Changement_de_silentbloc()
 * @method static self Changement_de_pneus()
 * @method static self Reparation_de_climatisation()
 * @method static self Changement_de_moteur()
 * @method static self Changement_de_radiateur()
 * @method static self Changement_de_culasse()
 * @method static self Changement_de_jointsCulasse()
 * @method static self Changement_de_pompeEau()
 * @method static self Changement_de_pompeCarburant()
 * @method static self Changement_de_bougies()
 * @method static self Changement_de_bobines()
 * @method static self Changement_de_courroies()
 * @method static self Changement_evaporateur()
 * @method static self Changement_de_compresseur()
 * @method static self Changement_de_condenseur()
 * @method static self Changement_de_ventilateur()
 * @method static self Changement_de_phares()
 * @method static self Changement_de_feux_arrieres()
 * @method static self Changement_de_retroviseurs()
 * @method static self Changement_de_boitevitesse()
 * @method static self Changement_de_pont_arriere()
 * @method static self Changement_de_pare_brise()
 * @method static self Changement_roulement_avant()
 * @method static self Changement_de_roulement_arriere()
 * @method static self Changement_de_moyeux()
 * @method static self Changement_de_disqueEmbrayage()
 * @method static self Changement_de_cylindre_de_freins()
 * @method static self Changement_de_cylindreEmbrayage()
 * @method static self Changement_de_maitre_cylindre()
 * @method static self Changement_de_batterie()
 * @method static self Changement_de_batterie()
 * @method static self Changement_de_batterie()
 * @method static self Changement_de_courroie()
 * @method static self Changement_de_phares()
 * @method static self Changement_de_clignotants()
 *                                                 =======================================================
 *                                                 AUTRES
 * @method static self Tolerie()
 * @method static self Peinture()
 *======================================================
 *                                AUTRES AUTRES XDXD
 * @method static self Inscription_des_produits_de_la_SPT()
 * @method static self Climatisation()
 * @method static self Revision_simple()
 */
class TypesReparation extends Enum
{
    protected static function values()
    {
        return function (string $name): string|int {

            $traductions = [
                'cremaillere' => 'crémaillère',
                'Reparation' => 'Réparation',
                'evaporateur' => 'd\'évaporateur',
                'arrieres' => 'arrière ',
                'retroviseurs' => 'rétroviseurs',
                'jointsCulasse' => 'joints de culasse',
                'pompeCarburant' => 'pompe à carburant',
                'boitevitesse' => 'boîte à vitesse',
                'arriere' => 'arrière',
                'disqueEmbrayage' => 'disque d\'embrayage',
                'cylindreEmbrayage' => 'cylindre d\'embrayage',
                'maitre' => 'maître',
                'filtreHuile' => 'filtre à huile',
                'filtreCarburant' => 'filtre à carburant',
                'pompeEau' => 'pompe à eau',
                'filtreAir' => 'pompe à air',
                'Revision_simple' => 'Révision simple à 5000Km',
            ];

            return strtr(str_replace('_', ' ', str($name)), $traductions);

        };
    }
}
