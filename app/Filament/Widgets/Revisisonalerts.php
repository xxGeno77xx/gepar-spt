<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Support\Database\StatesClass;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class Revisisonalerts extends BaseWidget
{
    protected static ?string $heading = 'Alertes révision';

    public static function canView(): bool
    {
        return false;
    }
    protected static ?int $sort = 4;

    protected function getTableQuery(): Builder
    {
        $alertingEngine = Engine::select('engines.id', 'remainder')
            ->where('state', '<>', StatesClass::Deactivated()->value)
            ->where('remainder', '>=', config('app.limitePourLaRevision'))
            ->select('engines.plate_number', 'engines.id', 'engines.remainder');

        return $alertingEngine;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('plate_number')
                ->label('Numéro de plaque'),

            BadgeColumn::make('remainder')
                ->label('Distance parcourue depuis la dernirère révision')
                ->formatStateUsing(fn (string $state): string => __("{$state} km"))
                ->color(static function ($record): string {

                    if ($record->remainder <= config('app.limitePourLaRevision')) {
                        return 'primary';
                    }

                    return 'danger';
                }),
        ];
    }
}
