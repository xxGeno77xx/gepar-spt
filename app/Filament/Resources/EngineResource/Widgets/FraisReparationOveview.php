<?php

namespace App\Filament\Resources\EngineResource\Widgets;

use App\Models\ConsommationCarburant;
use App\Models\Reparation;
use App\Support\Database\StatesClass;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class FraisReparationOveview extends BaseWidget
{
    public ?Model $record = null;

    protected function getCards(): array
    {

        $FraisDeReparation = Reparation::where('engine_id', $this->record->id)->where("validation_state", "=",StatesClass::NextValue()->value)->sum('cout_reparation');

        $Kilometrage = ConsommationCarburant::where('engine_id', $this->record->id)
            ->orderByDesc('id')
            ->first()
            ->kilometres_a_remplissage ?? $this->record->kilometrage_achat;

        $nombreDeReparations = Reparation::where('engine_id', $this->record->id)?->count();

        return [

            Card::make(new HtmlString('<p style="color:green; font-weight:bold; font-size:20px">Total des frais de réparation</p>'), number_format($FraisDeReparation, '0', ' ', '.').' FCFA')
                ->color('success')
                ->icon('heroicon-o-cash'),

            Card::make(new HtmlString('<p style="color:blue; font-weight:bold;  font-size:20px">Kilométrage actuel</p>'), $Kilometrage)
                ->color('success')
                ->icon('heroicon-o-chart-square-bar'),

            Card::make(new HtmlString('<p style="color:orange;font-weight:bold;  font-size:20px">Nombre de réparations</p>'), $nombreDeReparations)
                ->color('success')
                ->icon('heroicon-o-adjustments'),

        ];
    }
}
