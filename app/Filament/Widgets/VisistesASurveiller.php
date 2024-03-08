<?php

namespace App\Filament\Widgets;

use App\Models\Departement;
use App\Models\Direction;
use App\Models\Division;
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

class VisistesASurveiller extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {

        $limite = parametre::where('options', 'Visites techniques')->value('limite');

        $activated = StatesClass::Activated()->value;

        $visitesASurveiller = Engine::Join('visites', 'engines.id', '=', 'visites.engine_id')
            ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [$activated])
            ->whereRaw('TRUNC(visites.date_expiration) <= TRUNC(SYSDATE + TRUNC(?))', [$limite])
            ->where('visites.state', $activated)
            ->whereNull('visites.deleted_at')
            ->whereNull('engines.deleted_at')
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'centre.sigle',*/ 'marques.logo as logo', 'visites.date_initiale as date_initiale', 'visites.date_expiration as date_expiration')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'visites.date_expiration',
                'visites.date_initiale',
                'engines.id',
                'engines.modele_id',
                'engines.power',
                'engines.departement_id',
                'engines.price',
                'engines.circularization_date',
                'engines.date_aquisition',
                'engines.plate_number',
                'engines.type_id',
                'engines.car_document',
                'engines.carburant_id',
                'engines.assurances_mail_sent',
                'engines.visites_mail_sent',
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.carosserie',
                'engines.pl_ass',
                'engines.matricule_precedent',
                'engines.poids_total_en_charge',
                'engines.poids_a_vide',
                'engines.poids_total_roulant',
                'engines.Charge_utile',
                'engines.largeur',
                'engines.surface',
                'engines.couleur',
                'engines.date_cert_precedent',
                'engines.kilometrage_achat',
                'engines.numero_carte_grise',
                'engines.user_id',
                'engines.updated_at_user_id',
                'engines.deleted_at',
                'engines.created_at',
                'engines.updated_at',
                'sigle_centre',
                'nom_modele',
                'nom_marque',
                'logo',
                'remainder'

            )
            ->distinct();

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
                ->label('Département')
                ->tooltip(fn ($record) => Departement::find($record->departement_id)->libelle),

            // TextColumn::make('departement_id')
            //     ->label('Division/Direction')
            //     ->tooltip(fn($record) => (Division::find($record->departement_id))->libelle)
            //     ->searchable()
            //     ->placeholder('-')
            //     ->formatStateUsing(function ($state) {

            //         $division = Division::where('id', $state)->first();

            //         $direction = Direction::where('id', $division->direction_id)->value('sigle_direction');

            //         return $division->sigle_division.'/'.$direction;

            //     }),

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
