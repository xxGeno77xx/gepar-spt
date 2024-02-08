<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\Parametre;
use App\Support\Database\StatesClass;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {

        $visitesASurveiller = Engine::Join('visites', function ($join) {

            $limiteV = parametre::where('options', 'Visites techniques')->value('limite');

            $join->on('engines.id', '=', 'visites.engine_id')
                ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [StatesClass::Activated()->value])
                ->whereRaw("DATE(visites.date_expiration)<= DATE_ADD(CURDATE(), INTERVAL  $limiteV DAY) ")
                ->where('visites.state', StatesClass::Activated()->value)
                ->whereNull('visites.deleted_at');
        })
            ->select('engines.plate_number')
            ->where('engines.state', StatesClass::Activated()->value)
            ->distinct('engines.id');

        $assurancesASurveiller = Engine::Join('assurances', function ($join) {

            $limiteA = parametre::where('options', 'Assurances')->value('limite');

            $join->on('engines.id', '=', 'assurances.engine_id')
                ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state =?)', [StatesClass::Activated()->value])
                ->whereRaw("DATE(assurances.date_fin)<= DATE_ADD(CURDATE(), INTERVAL  $limiteA DAY) ")
                ->where('assurances.state', StatesClass::Activated()->value)
                ->whereNull('assurances.deleted_at');
        })
            ->select('engines.plate_number')
            ->where('engines.state', StatesClass::Activated()->value)
            ->distinct('engines.id');

        $enginesCloseToExpiry = 0; /* $visitesASurveiller->union($assurancesASurveiller)->distinct()->count();*/

        return [
            Card::make('Total des engins du parc', Engine::where('engines.state', StatesClass::Activated()->value)->count())
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('success'),

            Card::make('Engins à surveiller', $enginesCloseToExpiry)
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('danger'),

        ];
    }
}
