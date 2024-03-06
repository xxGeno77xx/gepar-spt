<?php

namespace App\Support\Database;

use Spatie\Enum\Enum;

/**
 * @method static self Declaration_initiale()
 * @method static self Demande_de_travail_Chef_division()
 * @method static self Demande_de_travail_directeur_division()
 * @method static self Demande_de_travail_dg()
 * @method static self Demande_de_travail_chef_parc()
 * @method static self Demande_de_travail_diga()
 * @method static self Bon_de_travail_chef_parc() //  mise en place du projet
 * @method static self Bon_de_travail_chef_division()
 * @method static self Bon_de_travail_budget()  //bon de travail
 * @method static self Bon_de_travail_Directeur_dvision()
 * @method static self Bon_de_travail_Directeur_general()
 * @method static self Bon_de_travail_retour_budget()  //bon de commande
 * @method static self Termine()   // par le chef parc
 * @method static self Rejete()
 */
class ReparationValidationStates extends Enum
{
    protected static function values()
    {

        return [
            'Declaration_initiale' => "Demande initiale",
            'Demande_de_travail_Chef_division' => "Validée par le chef Division",
            'Demande_de_travail_directeur_division' => "Validée par le Directeur ",
            'Demande_de_travail_dg' => "Validée par le DG",
            'Demande_de_travail_chef_parc' => "Validée par le chef Parc",
            'Demande_de_travail_diga' => "Validée par la DIGA",
            "Bon_de_travail_chef_parc" => "Bon de travail établi",
            "Bon_de_travail_chef_division" => "Bon validé par le chef division",
            "Bon_de_travail_budget" => "Bon validé par le Budget",
            "Bon_de_travail_Directeur_dvision" => "Bon validé par le Directeur de département",
            "Bon_de_travail_Directeur_general" => "Bon validé par le DG",
            "Bon_de_travail_retour_budget" => "Bon de commande établi",
            "Termine" => "Achevée",



        ];


    }
}
