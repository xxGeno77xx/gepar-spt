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

class AssurancesASurveiller extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getTableQuery(): Builder
    {

        $limite = parametre::orderBy('created_at', 'desc')->first()->value('limite');

        $limite = parametre::where('options', 'Assurances')->value('limite');

        $assurancesASurveiller = Engine::Join('assurances', function ($join) {

            $limite = parametre::where('options', 'Assurances')->value('limite');

            $join->on('engines.id', '=', 'assurances.engine_id')
                ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [StatesClass::Activated()->value])
                ->whereRaw("DATE(assurances.date_fin)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
                ->where('assurances.state', StatesClass::Activated()->value)
                ->whereNull('assurances.deleted_at');
        })
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            // ->join('departements', 'engines.departement_id', 'departements.id')
            // ->leftJoin('chauffeurs', 'engines.chauffeur_id', 'chauffeurs.id')
            // ->leftjoin('departements', 'chauffeurs.departement_id', 'departements.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'departements.nom_departement',*/ 'marques.logo as logo', 'assurances.date_debut as date_debut', DB::raw('DATE(assurances.date_fin) as date_fin'))
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->groupBy('engines.id', 'marques.nom_marque', 'assurances.date_debut', 'assurances.date_fin')
            ->distinct('engines.id');

        return $assurancesASurveiller;
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

            TextColumn::make('date_debut')
                ->label('Date de début')
                ->color('primary')
                ->searchable()
                ->dateTime('d-m-Y'),

            // TextColumn::make('date_fin')->label("Assurance (expiration)")->color('primary')->searchable(),
            BadgeColumn::make('date_fin')
                ->label('Date d\'expiration')
                ->color(static function ($record): string {

                    if (Carbon::parse($record->date_fin)->format('y-m-d') <= (Carbon::now()->format('y-m-d'))) {
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
        return 'Assurances à surveiller';
    }
}
