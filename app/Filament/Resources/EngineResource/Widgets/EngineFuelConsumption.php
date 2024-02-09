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

        $distances = $this->getRelatedKilometresValues();

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

    private function getRelatedKilometresValues()
    {

        $currentYear = date('Y');

        $sommeMensuelle = [];
        // $consommationsCollection  = ConsommationCarburant::select(
        //     DB::raw('EXTRACT(MONTH FROM "date") AS mois'),
        //     DB::raw('MAX(kilometres_a_remplissage) - MIN(kilometres_a_remplissage) AS difference_km')
        // )
        // ->groupBy(DB::raw('EXTRACT(MONTH FROM "date")'))
        // ->get();

        // foreach ($consommationsCollection as $item) {

        //     $month = date('M-y', strtotime($item->date));

        //     $sommeMensuelle[$month] = $item->distance_par_mois;
        // }

        return $sommeMensuelle;
    }
}
