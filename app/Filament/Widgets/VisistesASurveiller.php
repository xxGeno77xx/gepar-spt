<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\Parametre;
use App\Support\Database\StatesClass;
use App\Tables\Columns\DepartementColumn;
use Closure;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisistesASurveiller extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {

        $visitesASurveiller = Engine::Join('visites', function ($join) {

            $limite = parametre::where('options', 'Visites techniques')->value('limite');

            $join->on('engines.id', '=', 'visites.engine_id')
                ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?) ', [StatesClass::Activated()->value])
                ->whereRaw("DATE(visites.date_expiration)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
                ->where('visites.state', StatesClass::Activated()->value)
                ->whereNull('visites.deleted_at');
        })
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            // ->join('departements', 'engines.departement_id', 'departements.id')

            // ->leftJoin('chauffeurs', 'engines.chauffeur_id', 'chauffeurs.id')
            // ->leftjoin('departements', 'chauffeurs.departement_id', 'departements.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'departements.nom_departement',*/ 'marques.logo as logo', 'visites.date_initiale as date_initiale', DB::raw('DATE(visites.date_expiration) as date_expiration'))
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->groupBy('engines.id', 'marques.nom_marque', 'visites.date_initiale', 'visites.date_expiration')
            ->distinct('engines.id');

        return $visitesASurveiller;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('plate_number')
                ->label('Numéro de plaque')
                ->searchable(),

            DepartementColumn::make('departement_id')
                ->searchable()
                ->label('Département'),

            ImageColumn::make('logo')
                ->searchable()
                ->default(asset('images/default_product_image.jpg'))
                ->label('Marque')
                ->alignment('center'),

            TextColumn::make('date_initiale')
                ->label('Date de début')
                ->color('primary')
                ->searchable()
                ->dateTime('d-m-Y'),

            BadgeColumn::make('date_expiration')->label("Date d'expiration")
                ->color(static function ($record): string {
                    if (Carbon::parse($record->date_expiration)->format('y-m-d') <= Carbon::now()->format('y-m-d')) {
                        return 'danger';
                    }

                    return 'primary';
                })
                ->searchable()
                ->wrap()
                ->dateTime('d-m-Y'),
            BadgeColumn::make('state')
                ->label('Etat')
                ->color(static function ($record): string {

                    if ($record->state == StatesClass::Repairing()->value) {
                        return 'primary';
                    } else {
                        return 'success';
                    }
                }),

        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Engine $record): string => url('engines/'.$record->id.'/edit');
    }

    protected function getTableHeading(): string|Htmlable|Closure|null
    {
        return 'Visites techniques à surveiller';
    }
}
