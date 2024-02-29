<?php

namespace Database\Seeders;

use App\Models\Direction;
use App\Models\Division;
use Illuminate\Database\Seeder;

class DepartementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directions = collect([
            ['libelle' => 'DIRECTION GENERALE', 'sigle_direction' => 'DG'],
            ['libelle' => 'DIRECTION FINANCIERE ET COMPTABLE', 'sigle_direction' => 'DFC'],
            ['libelle' => 'DIRECTION DE LA CLIENTELE FINANCIERE', 'sigle_direction' => 'DCF'],
            ['libelle' => 'DIRECTION COMMERCIALE ET DE LA PLANIFICATION', 'sigle_direction' => 'DCP'],
            ['libelle' => 'DIRECTION DES RESSOURCES HUMAINES ET DU PATRIMOINE', 'sigle_direction' => 'DRHP'],
            ['libelle' => "DIRECTION DE L'INSPECTION GENERALE ET DE L'AUDIT", 'sigle_direction' => 'DIGA'],
            ['libelle' => 'DIRECTION DU COURRIER ET DU RESEAU', 'sigle_direction' => 'DCR'],
        ]);
        foreach ($directions as $direction) {
            Direction::create($direction);
        }

        $divisions = collect([
            //DG ok
            ['libelle' => 'Division Cellule Informatique', 'sigle_division' => 'CI', 'direction_id' => 1],
            ['libelle' => 'Division Secrétariat Central', 'sigle_division' => 'DSC', 'direction_id' => 1],
            // ["libelle" => "Division Fonction Conformité", "sigle_division" => "CI", "direction_id" => 1 ],

            //DFC ok
            ['libelle' => 'Division Comptabilité Générale', 'sigle_division' => 'DC', 'direction_id' => 2],
            ['libelle' => 'Division Contrôle de Gestion du Budget et de la Trésorerie', 'sigle_division' => 'DCGBT', 'direction_id' => 2],

            //DCF ok
            ['libelle' => 'Division Engagement et du Crédit', 'sigle_division' => 'DEC', 'direction_id' => 3],
            ['libelle' => 'Division Contrôle des Opérations Financières', 'sigle_division' => 'DCOF', 'direction_id' => 3],
            ['libelle' => 'Division Centre de Chèques Postaux et de l’Epargne', 'sigle_division' => 'DCCPE', 'direction_id' => 3],
            ['libelle' => 'Division des Transferts', 'sigle_division' => 'DT', 'direction_id' => 3],
            // ["libelle" => "Chargé de Mission auprès de la DCF", "sigle_division" => "CI", "direction_id" => 3 ],
            // ["libelle" => "Division Cellule des Services Financiers Numériques", "sigle_division" => "CI", "direction_id" => 3 ],

            //DCP ok
            // ["libelle" => "Chargé de Mission auprès de la DCP", "sigle_division" => "CI", "direction_id" => 4 ],
            ['libelle' => 'Division Qualité de Service et de la Planification', 'sigle_division' => 'DQSP', 'direction_id' => 4],
            ['libelle' => 'Division Commerciale et Marketing', 'sigle_division' => 'DCM', 'direction_id' => 4],
            // ["libelle" => "Division Régionale des Opérations Golfe", "sigle_division" => "CI", "direction_id" => 4 ],
            // ["libelle" => "Division Régionale des Opérations Savanes", "sigle_division" => "CI", "direction_id" => 4 ],
            // ["libelle" => "Division Régionale des Opérations Kara", "sigle_division" => "CI", "direction_id" => 4 ],
            // ["libelle" => "Division Régionale des Opérations Plateaux", "sigle_division" => "CI", "direction_id" => 4 ],
            // ["libelle" => "Division Régionale des Opérations Maritime", "sigle_division" => "CI", "direction_id" => 4 ],
            // ["libelle" => "Division Régionale des Opérations Centrale", "sigle_division" => "CI", "direction_id" => 4 ],

            //DRHP ok
            ['libelle' => 'Division Patrimoine et Logistique', 'sigle_division' => 'DPL', 'direction_id' => 5],
            ['libelle' => 'Division du Personnel et des Affaires Sociales', 'sigle_division' => 'DPAS', 'direction_id' => 5],

            //DIGA ok
            ['libelle' => 'Division Cellule Inspection Générale', 'sigle_division' => 'CIG', 'direction_id' => 6],
            ['libelle' => 'Division Cellule Audit Interne', 'sigle_division' => 'CAI', 'direction_id' => 6],

            //DCR ok
            ['libelle' => 'Division Acheminement et Transport', 'sigle_division' => 'DDAT', 'direction_id' => 7],
            ['libelle' => 'Division Centre Nation du Tri Postal', 'sigle_division' => 'CNTP', 'direction_id' => 7],
            // ["libelle" => "Division Courrier d’Entreprise", "sigle_division" => "CI", "direction_id" => 7 ],
            ['libelle' => 'Division Express Mail - TOGO', 'sigle_division' => 'DEMS', 'direction_id' => 7],
            // ["libelle" => "Division Recette Principale", "sigle_division" => "CI", "direction_id" => 7 ],
        ]);

        foreach ($divisions as $division) {
            Division::create($division);
        }

    }
}
