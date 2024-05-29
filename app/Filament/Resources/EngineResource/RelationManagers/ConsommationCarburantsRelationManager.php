<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Chauffeur;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use App\Models\ConsommationCarburant;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Resources\RelationManagers\RelationManager;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

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

                        Toggle::make("especes")
                            ->label("Prise en especes")
                            ->reactive()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(function () {

                            }),
                        Forms\Components\DatePicker::make('date_prise')
                            // ->beforeOrEqual(date_format(now(), 'd-m-Y')) // to do: make it unique for every engine
                            ->required(),

                        Forms\Components\Hidden::make('state')
                            ->default(StatesClass::Activated()->value),

                        Forms\Components\TextInput::make('ticket')
                            ->unique(ignoreRecord: true)
                            ->visible(fn($get) => $get("especes") == 1 ? false : true)
                            ->required(fn($get) => $get("especes") == 1 ? false : true),



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
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn($get, $set) => $set("montant_total", $get("prix_unitaire") * $get("quantite"))),

                        Forms\Components\TextInput::make('prix_unitaire')
                            ->suffix('FCFA')
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(fn($get, $set) => $set("montant_total", $get("prix_unitaire") * $get("quantite"))),

                        Forms\Components\TextInput::make('montant_total')
                            ->suffix('FCFA')
                            ->disabled()
                            ->dehydrated()
                            ->numeric(),

                        Forms\Components\TextInput::make('kilometres_a_remplissage')
                            ->label('Indice compteur')
                            ->numeric()
                            ->suffix('Km')
                            ->minValue(0)
                            ->required()
                            ->rules([
                                function (RelationManager $livewire, $state, $record) {

                                    return function (string $attribute, $value, Closure $fail) use ($livewire, $state, $record) {

                                        $latestConsommation = ConsommationCarburant::orderBy('id', 'desc')
                                            ->where('engine_id', $livewire->ownerRecord->id)
                                            ->first();

                                        $forelast = ConsommationCarburant::orderBy('id', 'desc')
                                            ->where('engine_id', $livewire->ownerRecord->id)
                                            ->skip(1)
                                            ->take(1)
                                            ->first();

                                        // dd($forelast,  $latestConsommation , $state ,  $latestConsommation->kilometres_a_remplissage >= $state);
                        
                                        if ($latestConsommation) {

                                            if ($record) {

                                                if ($forelast) {
                                                    if ($forelast->kilometres_a_remplissage >= $state) {

                                                        $fail('Le  kilométrage précédent était de ' . $forelast->kilometres_a_remplissage . ' km');
                                                    }
                                                } else {

                                                    if ($livewire->ownerRecord->kilometrage_achat >= $state) {

                                                        $fail('Le kilométrage à l\'achat était de ' . $livewire->ownerRecord->kilometrage_achat . ' km');
                                                    }

                                                }

                                            } else {

                                                if ($latestConsommation->kilometres_a_remplissage >= $state) {

                                                    $fail('Le dernier kilométrage  était de ' . $latestConsommation->kilometres_a_remplissage . ' km');
                                                }
                                            }
                                        } else {
                                            if ($livewire->ownerRecord->kilometrage_achat >= $state) {

                                                $fail('Le kilométrage à l\'achat était de ' . $livewire->ownerRecord->kilometrage_achat . ' km');
                                            }
                                        }

                                    };
                                },
                            ]),

                        Forms\Components\Select::make('chauffeur_id')
                            ->label('Chauffeur')
                            ->options(
                                Chauffeur::select(['fullname', 'id'])->get()->pluck('fullname', 'id')
                            )
                            ->searchable()
                            ->reactive()
                            ->required(fn($get, $set) => $get('conducteur') ? false : true),

                        Forms\Components\TextInput::make('conducteur')
                            ->reactive()
                            ->required(fn($get, $set) => $get('chauffeur_id') ? false : true),


                        // ->visible(fn($get , $set) => $get("especes") == 1 ? false : true),

                    ]),

               Grid::make(2)
               ->schema([
                Forms\Components\TextInput::make('carte_recharge_id')
                ->label('Carte de recharge'),

            Forms\Components\TextInput::make('observation'),
               ]),

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
                Tables\Columns\TextColumn::make('date_prise')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ticket')
                    ->label('Ticket N°'),

                Tables\Columns\TextColumn::make('kilometres_a_remplissage')
                    ->label('Indice compteur'),

                Tables\Columns\TextColumn::make('fullname')
                    ->label('Chauffeur')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('conducteur')
                    ->label('Conducteur')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('observation')
                    ->limit(8)
                    ->label('Observation')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('quantite')
                    ->label('Quantité (L)'),

                Tables\Columns\TextColumn::make('prix_unitaire')
                    ->label('Prix unitaire'),

                Tables\Columns\TextColumn::make('montant_total')
                    ->formatStateUsing(fn($state) => $state . " FCFA")
                    ->label('Montant total'),
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
                                fn(Builder $query, $date): Builder => $query->whereDate('date_prise', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_prise', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date: du  ' . Carbon::parse($data['date_from'])->format('d-m-Y') . ' au ' . Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),

                Filter::make('Chauffeur')
                    ->form([
                        Select::make('chauffeur_id')
                            ->searchable()
                            ->label('Chauffeur')
                            ->options(Chauffeur::pluck('fullname', 'id')),

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
                        if (!$data['chauffeur_id']) {
                            return null;
                        }

                        return 'Chauffeur: ' . Chauffeur::where('chauffeurs.id', $data['chauffeur_id'])->value('fullname');
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()

                    ->label('Ajouter')
                    ->after(function (RelationManager $livewire, $data) {

                        $latestConsommation = ConsommationCarburant::orderBy('id', 'desc')
                            ->where('engine_id', $livewire->ownerRecord->id)
                            ->first();

                        $forelast = ConsommationCarburant::orderBy('id', 'desc')
                            ->where('engine_id', $livewire->ownerRecord->id)
                            ->skip(1)->take(1)
                            ->first();

                        $currentRemainder = $livewire->ownerRecord->remainder;

                        if ($latestConsommation && $forelast) {

                            $livewire->ownerRecord->update(['remainder' => $currentRemainder + ($latestConsommation->kilometres_a_remplissage - $forelast->kilometres_a_remplissage)]);

                        }

                    }),

                Tables\Actions\Action::make('export')

                    ->label('Récapitulatif'),

                FilamentExportHeaderAction::make('export')
                    ->label('Exporter')
                    ->hidden(fn(RelationManager $livewire, $action) => count(ConsommationCarburant::where('engine_id', '=', $livewire->ownerRecord->id)->get()) >= 1 ? false : true)
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

                        $releve = 0;

                        $periodeDebut = Carbon::parse($action->getRecords()->min('date_prise'))->format('d-m-Y');

                        $periodeFin = Carbon::parse($action->getRecords()->max('date_prise'))->format('d-m-Y');

                        $consoMoyenne = number_format((float) (($action->getRecords()->sum('quantite')) / ($action->getRecords()->count())), 2, '.', '');

                        $premierIndice = ConsommationCarburant::where('id', $action->getRecords()->min('id'))->first()->kilometres_a_remplissage;

                        $dernierIndice = ConsommationCarburant::where('id', $action->getRecords()->max('id'))->first()->kilometres_a_remplissage;

                        $total = $action->getRecords()->sum('quantite');

                        $consoAuCent = round((($dernierIndice - $premierIndice) / $total), 2);

                        return [

                            'plate_number' => $livewire->ownerRecord->plate_number,
                            'modele' => $OwnerEngine->modele,
                            'marque' => $OwnerEngine->marque,
                            'type' => $OwnerEngine->type,
                            'carburant' => $OwnerEngine->carburant,
                            'departement' => $OwnerEngine->departement,
                            'total' => $total,
                            'debutPeriode' => $periodeDebut,
                            'finPeriode' => $periodeFin,
                            'consoMoyenne' => $consoMoyenne,
                            'releve' => $releve,
                            'consoAuCent' => $consoAuCent,
                        ];
                    }),

            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->hidden(function ($record, RelationManager $livewire) {

                        $islastForEngine = ConsommationCarburant::where('engine_id', $livewire->ownerRecord->id)->max('id');

                        if ($record->id == $islastForEngine) {

                            return false;
                        }

                        return true;
                    })->before(function (RelationManager $livewire, $data, $record) {

                        $latestConsommation = ConsommationCarburant::orderBy('id', 'desc')
                            ->where('engine_id', $livewire->ownerRecord->id)
                            ->first();

                        $forelast = ConsommationCarburant::orderBy('id', 'desc')
                            ->where('engine_id', $livewire->ownerRecord->id)
                            ->skip(1)->take(1)
                            ->first();

                        $currentRemainder = $livewire->ownerRecord->remainder;

                        if ($latestConsommation && $forelast) {

                            $livewire->ownerRecord->update(['remainder' => $currentRemainder - ($latestConsommation->kilometres_a_remplissage - $forelast->kilometres_a_remplissage) + ($data['kilometres_a_remplissage'] - $forelast->kilometres_a_remplissage)]);

                        }

                    }),

                // Tables\Actions\Action::make('Supprimer')
                //     ->hidden(!auth()->user()->hasRole(RolesPermissionsSeeder::SuperAdmin))
                //     ->color('danger')
                //     ->icon('heroicon-o-x')
                //     ->action(fn($record) => $record->update(['state' => StatesClass::Deactivated()->value]))
                //     ->requiresConfirmation(),
            ])
            ->bulkActions([

            ])
            ->defaultSort('date_prise', 'asc');
    }

    public function getTableQuery(): Builder
    {
        return ConsommationCarburant::leftJoin('chauffeurs', 'consommation_carburants.chauffeur_id', 'chauffeurs.id')
            ->leftJoin('engines', 'consommation_carburants.engine_id', 'engines.id')
            ->select(['consommation_carburants.*', 'chauffeurs.fullname'])
            ->where('consommation_carburants.state', StatesClass::Activated()->value)
            ->where('engines.id', $this->ownerRecord->id);
    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return null;
    }
}
