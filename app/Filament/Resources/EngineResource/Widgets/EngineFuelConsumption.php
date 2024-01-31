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

        $consommation = $this->getRelatedConsommationValues();

        $distances = $this->getRelatedKilometresValues();

        return [
            'datasets' => [
                [
                    'label' => 'Kilométrage',
                    'data' => $distances,
                    'backgroundColor' => 'blue',
                    'borderColor' => 'blue',
                ],

                [
                    'label' => 'Consommation en litres de carburant',
                    'data' => $consommation,
                    'backgroundColor' => 'primary',
                    'borderColor' => 'primary',
                ],
            ],
            // 'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    private function getRelatedConsommationValues()
    {
        $sommeParMois = [];
        $consommationsCollection = ConsommationCarburant::where('engine_id', $this->record->id)
            ->orderBy('date', 'asc')
            ->get();

        $sommeMensuelle = [];

        foreach ($consommationsCollection as $item) {

            $month = date('M-y', strtotime($item->date));

            $sommeMensuelle[$month] = ($sommeMensuelle[$month] ?? 0) + $item['quantite'];
        }

        return $sommeMensuelle;

    }

    private function getRelatedKilometresValues()
    {

        $sommeMensuelle = [];

        $consommationsCollection = ConsommationCarburant::select(
            DB::raw('MONTH(date) as mois'),
            DB::raw('YEAR(date) as annee'),
            DB::raw('ANY_VALUE((SELECT kilometres_a_remplissage FROM consommation_carburants as c1 WHERE MONTH(c1.date) = MONTH(consommation_carburants.date) AND YEAR(c1.date) = YEAR(consommation_carburants.date) AND c1.engine_id = consommation_carburants.engine_id ORDER BY date ASC LIMIT 1)) as premier_kilometrage'),
            DB::raw('ANY_VALUE((SELECT kilometres_a_remplissage FROM consommation_carburants as c2 WHERE MONTH(c2.date) = MONTH(consommation_carburants.date) AND YEAR(c2.date) = YEAR(consommation_carburants.date) AND c2.engine_id = consommation_carburants.engine_id ORDER BY date DESC LIMIT 1)) as dernier_kilometrage'),
            DB::raw('ANY_VALUE((SELECT dernier_kilometrage - premier_kilometrage)) as distance_par_mois'),
            'date'
        )
            ->where('consommation_carburants.engine_id', $this->record->id)
            ->groupBy('mois', 'annee', 'date')
            ->orderBy('mois', 'asc')
            ->orderBy('annee', 'asc')
            ->get();

        foreach ($consommationsCollection as $item) {

            $month = date('M-y', strtotime($item->date));

            $sommeMensuelle[$month] = $item->distance_par_mois;
        }

        return $sommeMensuelle;
    }
}
