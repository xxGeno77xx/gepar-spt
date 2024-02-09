<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Models\Chauffeur;
use App\Models\ConsommationCarburant;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ConsommationCarburantsRelationManager extends RelationManager
{
    protected static string $relationship = 'consommationCarburants';

    protected static ?string $title = 'Carburant & kilometrage';

    protected static ?string $recordTitleAttribute = '';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Grid::make(3)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->beforeOrEqual(date_format(now(), 'd-m-Y')) // to do: make it unique for every engine
                            ->required(),

                        Forms\Components\Hidden::make('state')
                            ->default(StatesClass::Activated()->value),

                        Forms\Components\TextInput::make('ticket')
                            ->unique(ignoreRecord: true)
                            ->required(),

                        Forms\Components\TextInput::make('quantite')
                            ->label('quantité')
                            ->suffix('Litres')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if ($value < 0) {
                                            $fail('Le champ :attribute doit être supérieur à 0.');
                                        }
                                    };
                                },
                            ]),

                        Forms\Components\TextInput::make('kilometres_a_remplissage')
                            ->label('Kilometrage au remplissage')
                            ->numeric()
                            ->suffix('Km')
                            ->minValue(0)
                            ->required()
                            ->rules([
                                function (RelationManager $livewire) {

                                    return function (string $attribute, $value, Closure $fail) use ($livewire) {

                                        $latestConsommation = ConsommationCarburant::latest()
                                            ->where('engine_id', $livewire->ownerRecord->id)
                                            ->first();

                                        if ($latestConsommation) {

                                            if ($value <= $latestConsommation->kilometres_a_remplissage) {
                                                // $fail('Le champ :attribute doit être supérieur à 0.');
                                                $fail('Le dernier kilométrage était à '.$latestConsommation->kilometres_a_remplissage.' km');
                                            }
                                        }

                                    };
                                },
                            ]),

                        Forms\Components\Select::make('chauffeur_id')
                            ->label('Chauffeur')
                            ->options(
                                Chauffeur::select(['name', 'id'])->get()->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('carte_recharge_id')
                            ->label('Carte de recharge')
                            ->required(),

                    ]),
                Forms\Components\TextInput::make('observation')
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('carburant_id')
                    ->default(function (RelationManager $livewire): int {
                        return $livewire->ownerRecord->carburant()
                            ->value('id');
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->sortable()
                    ->label('Date')
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('ticket')
                    ->label('Ticket N°'),

                Tables\Columns\TextColumn::make('kilometres_a_remplissage')
                    ->label('Indice compteur'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Chauffeur'),

                Tables\Columns\TextColumn::make('observation')
                    ->limit(8)
                    ->label('Observation')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('quantite')
                    ->label('Quantité (L)'),
            ])
            ->filters([
                Filter::make('date_lancement')
                    ->label('Date')
                    ->form([
                        Grid::make(2)
                            ->schema([

                                DatePicker::make('date_from')
                                    ->label('Du'),

                                DatePicker::make('date_to')
                                    ->label('Au'),

                            ])->columns(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date: du  '.Carbon::parse($data['date_from'])->format('d-m-Y').' au '.Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),

                Filter::make('Chauffeur')
                    ->form([
                        Select::make('chauffeur_id')
                            ->searchable()
                            ->label('Chauffeur')
                            ->options(Chauffeur::pluck('name', 'id')),

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['chauffeur_id'],
                                function (Builder $query, $status) {

                                    $search = Chauffeur::where('id', $status)->value('id');

                                    return $query->where('chauffeur_id', $search);
                                }
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (! $data['chauffeur_id']) {
                            return null;
                        }

                        return 'Chauffeur: '.Chauffeur::where('chauffeurs.id', $data['chauffeur_id'])->value('name');
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Ajouter'),
                Tables\Actions\Action::make('export')
                    ->label('Récapitulatif'),

                FilamentExportHeaderAction::make('export')
                    ->label('Exporter')
                    ->disablePdf()
                    ->extraViewData(function (RelationManager $livewire, $action) {

                        $OwnerEngine = $livewire->ownerRecord->join('modeles', 'modeles.id', 'engines.modele_id')
                            ->leftJoin('marques', 'marques.id', 'modeles.marque_id')
                            ->leftJoin('centre', 'centre.code_centre', 'engines.departement_id')
                            ->leftJoin('types_engins', 'engines.type_id', 'types_engins.id')
                            ->leftJoin('carburants', 'carburants.id', 'engines.carburant_id')
                            ->select([
                                'engines.*',
                                'modeles.nom_modele as modele',
                                'marques.nom_marque as marque',
                                'types_engins.nom_type as type',
                                'carburants.type_carburant as carburant',
                                'centre.sigle_centre  as departement',
                            ])
                            ->where('engines.id', $livewire->ownerRecord->id)
                            ->first();

                        $total = $action->getRecords()->sum('quantite');

                        return [

                            'plate_number' => $livewire->ownerRecord->plate_number,
                            'modele' => $OwnerEngine->modele,
                            'marque' => $OwnerEngine->marque,
                            'type' => $OwnerEngine->type,
                            'carburant' => $OwnerEngine->carburant,
                            'departement' => $OwnerEngine->departement,
                            'total' => $total,
                        ];
                    }),

            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('Supprimer')
                    ->action(fn () => $record->update(['state' => StatesClass::Deactivated()->value])),
            ])
            ->bulkActions([

            ])
            ->defaultSort('date', 'asc');
    }

    public function getTableQuery(): Builder
    {
        return ConsommationCarburant::leftJoin('chauffeurs', 'consommation_carburants.chauffeur_id', 'chauffeurs.id')
            ->leftJoin('engines', 'consommation_carburants.engine_id', 'engines.id')
            ->select(['consommation_carburants.*', 'chauffeurs.name'])
            ->where('consommation_carburants.state', StatesClass::Activated()->value)
            ->where('engines.id', $this->ownerRecord->id);
    }
}
