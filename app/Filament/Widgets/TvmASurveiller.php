<?php

namespace App\Filament\Widgets;

use Closure;
use Carbon\Carbon;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Parametre;
use App\Models\Departement;
use App\Support\Database\StatesClass;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Tables\Columns\DepartementColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Widgets\TableWidget as BaseWidget;

class TvmASurveiller extends BaseWidget
{

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string | Htmlable | Closure | null
    {
        return "TVM à surveiller" ;
    }

    protected function getTableQuery(): Builder
    {
        $limite = Parametre::where('options', 'Tvm')->value('limite');

        $activated = StatesClass::Activated()->value;


        $tvmASurveiller = Engine::Join('tvms', 'engines.id', '=', 'tvms.engine_id')
            ->whereRaw('tvms.created_at = (SELECT MAX(created_at) FROM tvms WHERE engine_id = engines.id AND tvms.state = ?)', [$activated])
            ->whereRaw('TRUNC(tvms.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limite])
            ->where('tvms.state', $activated)
            ->whereNull('tvms.deleted_at')
            ->whereNull('engines.deleted_at')
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'centre.sigle',*/ 'marques.logo as logo', 'tvms.date_debut as date_debut', 'tvms.date_fin as date_fin')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'tvms.date_fin',
                'tvms.date_debut',
                'engines.tvm_mail_sent',
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
                'engines.charge_utile',
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

            return $tvmASurveiller;
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

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Engine $record): string => url('engines/'.$record->id.'/edit');
    }
}
