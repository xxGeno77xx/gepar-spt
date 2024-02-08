<?php

namespace App\Filament\Resources\EngineResource\Widgets;

use App\Models\ConsommationCarburant;
use App\Models\Engine;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class EngineFuelConsumption extends LineChartWidget
{
    protected static ?string $heading = 'Chart';

    protected int|string|array $columnSpan = 'full';

    public ?Engine $record = null;

    protected function getHeading(): string
    {

        return 'Consommation mensuelle de carburant';
    }

    protected function getData(): array
    {

        // $consommation = $this->getRelatedConsommationValues();

        // $distances = $this->getRelatedKilometresValues();

        return [
            'datasets' => [
                [
                    'label' => 'Distance parcourue ce mois',
                    'data' => [],
                    'backgroundColor' => 'blue',
                    'borderColor' => 'blue',
                ],

                [
                    'label' => 'Consommation en litres de carburant',
                    'data' => [],
                    'backgroundColor' => 'primary',
                    'borderColor' => 'primary',
                ],
            ],
            // 'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    // private function getRelatedConsommationValues()
    // {
    //     $sommeParMois = [];
    //     $consommationsCollection = ConsommationCarburant::where('engine_id', $this->record->id)
    //         ->orderBy('date', 'asc')
    //         ->get();

    //     $sommeMensuelle = [];

    //     foreach ($consommationsCollection as $item) {

    //         $month = date('M-y', strtotime($item->date));

    //         $sommeMensuelle[$month] = ($sommeMensuelle[$month] ?? 0) + $item['quantite'];
    //     }

    //     return $sommeMensuelle;

    // }

    // private function getRelatedKilometresValues()
    // {

    //     $currentYear = date('Y');

    //     $sommeMensuelle = [];
    //     $consommationsCollection = ConsommationCarburant::select(
    //         DB::raw('EXTRACT(MONTH FROM date) as mois'),
    //         DB::raw('EXTRACT(YEAR FROM date) as annee'),
    //         DB::raw('(SELECT MIN(kilometres_a_remplissage) FROM consommation_carburants c1 WHERE EXTRACT(MONTH FROM c1.date) = EXTRACT(MONTH FROM consommation_carburants.date) AND EXTRACT(YEAR FROM c1.date) = EXTRACT(YEAR FROM consommation_carburants.date) AND c1.engine_id = consommation_carburants.engine_id) as premier_kilometrage'),
    //         DB::raw('(SELECT MAX(kilometres_a_remplissage) FROM consommation_carburants c2 WHERE EXTRACT(MONTH FROM c2.date) = EXTRACT(MONTH FROM consommation_carburants.date) AND EXTRACT(YEAR FROM c2.date) = EXTRACT(YEAR FROM consommation_carburants.date) AND c2.engine_id = consommation_carburants.engine_id) as dernier_kilometrage'),
    //         DB::raw('(SELECT MAX(kilometres_a_remplissage) - MIN(kilometres_a_remplissage) FROM consommation_carburants c3 WHERE EXTRACT(MONTH FROM c3.date) = EXTRACT(MONTH FROM consommation_carburants.date) AND EXTRACT(YEAR FROM c3.date) = EXTRACT(YEAR FROM consommation_carburants.date) AND c3.engine_id = consommation_carburants.engine_id) as distance_par_mois'),
    //         'date'
    //     )
    //         ->where('consommation_carburants.engine_id', $this->record->id)
    //         ->whereYear('date', $currentYear)
    //         ->groupBy(DB::raw('EXTRACT(MONTH FROM date)'), DB::raw('EXTRACT(YEAR FROM date)'), 'date')
    //         ->orderBy('mois', 'asc')
    //         ->orderBy('annee', 'asc')
    //         ->get();
        
        
    

    //     foreach ($consommationsCollection as $item) {

    //         $month = date('M-y', strtotime($item->date));

    //         $sommeMensuelle[$month] = $item->distance_par_mois;
    //     }

    //     return $sommeMensuelle;
    // }
}
