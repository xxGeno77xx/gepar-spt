<?php

namespace App\Filament\Widgets;

use App\Models\ConsommationCarburant;
use App\Models\Engine;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ConsommationCarburantCart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static string $chartId = 'lineColumnChart';

    protected static string $engine = '';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Consommation & kilométrage';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $dateStart = $this->filterFormData['date_start'];
        $dateEnd = $this->filterFormData['date_end'];
        $engine = $this->filterFormData['plate_number']; // is actually engine.id

        if (isset($this->filterFormData['plate_number'])) {

            self::$heading = 'Consommation & kilométrage '.Engine::where('id', $this->filterFormData['plate_number'])->first()->plate_number;
        }

        $conso = ConsommationCarburant::join('engines', 'engines.id', 'consommation_carburants.engine_id')
            ->whereBetween('date_prise', [$dateStart, $dateEnd])
            ->where('engines.id', $engine)
            ->select('quantite', 'kilometres_a_remplissage', 'date_prise', DB::raw('EXTRACT(MONTH FROM date_prise) AS mois'))  //DB::raw('EXTRACT(MONTH FROM date_prise) AS mois') put this instead in oracle
            ->orderBy('date_prise', 'asc')
            ->get();

        $consommationsMoyennes = ConsommationCarburant::join('engines', 'engines.id', 'consommation_carburants.engine_id')
            ->whereBetween('date_prise', [$dateStart, $dateEnd])
            ->where('engines.id', $engine)
            ->selectRaw('EXTRACT(MONTH FROM date_prise) as month, AVG(quantite) as moyenne_quantite, MAX(kilometres_a_remplissage) - MIN(kilometres_a_remplissage) as distance_parcourue')
            ->groupBy(DB::raw('EXTRACT(MONTH FROM date_prise)'))
            ->orderBy('month', 'asc')
            ->get();

        $kiloArray = [];

        $montsArray = []; //months present for a given engine

        $averagesArray = [];

        if ($conso) {
            foreach ($conso as $key => $consommation) {

                // $consoArray[] = $consommation->quantite;

                $currentConsommationMonth = Carbon::parse($consommation->date_prise)->translatedFormat('M y');

                if (! in_array($currentConsommationMonth, $montsArray)) {
                    $montsArray[] = Carbon::parse($consommation->date_prise)->translatedFormat('M y');
                }

            }

        }

        foreach ($consommationsMoyennes as $moyenneMensuelle) {
            $averagesArray[] = round($moyenneMensuelle->moyenne_quantite, 2);

            $kiloArray[] = round($moyenneMensuelle->distance_parcourue, 2);

        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Moyennes de carburant',
                    'data' => $averagesArray,
                    'type' => 'column',
                ],
                [
                    'name' => 'Kilométrage par mois',
                    'data' => $kiloArray,
                    'type' => 'line',
                ],
            ],
            'stroke' => [
                'width' => [0, 4],
                'curve' => 'smooth',
            ],
            'xaxis' => [
                'categories' => $montsArray, //['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'colors' => '#9ca3af',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'colors' => '#9ca3af',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'legend' => [
                'labels' => [
                    'colors' => '#9ca3af',
                    'fontWeight' => 600,
                ],
            ],
            'colors' => ['#6366f1', '#38bdf8'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => ['#d946ef'],
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => [0, 100],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 10,
                ],
            ],
        ];

    }

    protected function getFormSchema(): array
    {
        return [

            Placeholder::make('Filtrer'),

            DateTimePicker::make('date_start')
                ->label('Date début'),

            DateTimePicker::make('date_end')
                ->label('Date fin'),

            Select::make('plate_number')
                ->label('Numéro de plaque')
                ->searchable()
                ->options(
                    Engine::select(['plate_number', 'id'])
                        ->pluck('plate_number', 'id')
                ),

        ];
    }
}
