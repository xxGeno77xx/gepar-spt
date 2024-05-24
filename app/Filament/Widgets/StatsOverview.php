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

        $activated = StatesClass::Activated()->value;

        $limiteA = parametre::where('options', 'Assurances')->value('limite');

        $limiteV = parametre::where('options', 'Visites techniques')->value('limite');

        $limiteT = parametre::where('options', 'Tvm')->value('limite');

        $visites = Engine::Join('visites', 'engines.id', '=', 'visites.engine_id')
            ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [$activated])
            ->whereRaw('TRUNC(visites.date_expiration) <= TRUNC(SYSDATE + TRUNC(?))', [$limiteV])
            ->where('visites.state', $activated)
            ->whereNull('visites.deleted_at')
            ->whereNull('engines.deleted_at')
            ->whereNull('engines.deleted_at')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->count();

        $assurances = Engine::Join('assurances', 'engines.id', '=', 'assurances.engine_id')
            ->whereNull('engines.deleted_at')
            ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [$activated])
            ->whereRaw('TRUNC(assurances.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limiteA])
            ->where('assurances.state', $activated)
            ->whereNull('assurances.deleted_at')
            ->whereNull('engines.deleted_at')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->count();

        $tvms = Engine::Join('tvms', 'engines.id', '=', 'tvms.engine_id')
            ->whereNull('engines.deleted_at')
            ->whereRaw('tvms.created_at = (SELECT MAX(created_at) FROM tvms WHERE engine_id = engines.id AND tvms.state = ?)', [$activated])
            ->whereRaw('TRUNC(tvms.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limiteT])
            ->where('tvms.state', $activated)
            ->whereNull('tvms.deleted_at')
            ->whereNull('engines.deleted_at')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->count();

        return [
            // Card::make('Total des engins du parc', Engine::where('engines.state', '<>', StatesClass::Deactivated()->value)->count()) //  to do:  where activated  or reparing
            //     ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
            //     ->color('success'),

            Card::make('TVM à surveiller', $tvms) //  to do:  where activated  or reparing
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('danger'),

            Card::make('Visites techniques à surveiller', $visites)
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('danger'),

            Card::make('Assurances à surveiller', $assurances)
                ->chart([mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50), mt_rand(1, 50)])
                ->color('danger'),
        ];
    }
}
