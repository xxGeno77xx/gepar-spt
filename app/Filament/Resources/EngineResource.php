<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EngineResource\Pages;
use App\Filament\Resources\EngineResource\RelationManagers;
use App\Filament\Resources\EngineResource\RelationManagers\AffectationsRelationManager;
use App\Filament\Resources\EngineResource\RelationManagers\ConsommationCarburantsRelationManager;
use App\Filament\Resources\EngineResource\RelationManagers\OrdreDeMissionsRelationManager;
use App\Filament\Resources\EngineResource\RelationManagers\TvmsRelationManager;
use App\Models\Carburant;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Division;
use App\Models\Engine;
use App\Models\Engine as Engin;
use App\Models\Modele;
use App\Models\Type;
use App\Support\Database\CommonInfos;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use App\Tables\Columns\DepartementColumn;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class EngineResource extends Resource
{
    protected static ?string $model = Engin::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $modelLabel = 'Engins';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Card::make()
                    ->schema([

                        TextInput::make('matricule_precedent')
                            ->label('Matricule précédent')
                            ->regex('/^(RTG|TG)-\d{4}-[A-Z]{2}$/')
                            ->placeholder('TG 1234-AB')
                            ->maxLength(12)
                            ->unique(ignoreRecord: true),

                        TextInput::make('plate_number')
                            ->label('Numéro de plaque')
                            ->placeholder('TG-1234-AB ou RTG-1234')
                            ->regex('/^(RTG|TG)-\d{4}-[A-Z]{2}$/')
                            ->maxLength(12)
                            ->required()
                            // ->unique(ignoreRecord: true)
                            ->rules([
                                function ($record) {

                                    return function (string $attribute, $value, Closure $fail) use ($record) {

                                        $existingEngine = Engine::where('plate_number', $value)->first();

                                        if ($existingEngine && $record) {
                                            if ($existingEngine->id != $record->id) {
                                                $fail('Un engin avec ce numéro de plaque existe déjà.');
                                            }
                                        } elseif ($existingEngine) {
                                            $fail('Un engin avec ce numéro de plaque existe déjà.');
                                        }
                                    };
                                },
                            ]),

                        DatePicker::make('circularization_date')
                            ->label('Mise en circulation'),

                        DatePicker::make('date_aquisition')
                            ->label("Date d'acquisition")
                            ->required(),

                        TextInput::make('price')
                            ->label("Prix d'achat")
                            ->suffix('FCFA')
                            ->numeric()
                            ->required(),

                        TextInput::make('kilometrage_achat')
                            ->label("Kilométrage à l'achat")
                            ->minValue(0)
                            ->required()
                            ->numeric(),

                        // Select::make('activite_id')
                        //     ->label('Activité')
                        //     ->options(
                        //         [
                        //             'Activité postale' => 'Activité postale',
                        //             'Activité  financière' => 'Activité financière',
                        //             'Activité mixte' => 'Activité mixte',

                        //         ]
                        //     ) // activité de l'engin:  mixte/financière/postale
                        //     ->searchable()
                        //     ->dehydrated(false)
                        //     ->required()
                        //     ->columnSpanFull(),

                        Grid::make(6)
                            ->schema([
                                TextInput::make('power')
                                    ->label('Puissance')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('pl_ass')
                                    ->label('pl_ass')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('numero_chassis')
                                    ->label('Numéro de chassis')
                                    // ->unique(ignoreRecord: true)
                                    ->required()
                                    ->rules([
                                        function ($record) {
                                            return function (string $attribute, $value, Closure $fail) use ($record) {

                                                $existingEngine = Engine::where('numero_chassis', $value)->first();

                                                if ($existingEngine && $record) {
                                                    if ($existingEngine->id != $record->id) {
                                                        $fail('Un engin avec ce numéro de chassis existe déjà.');
                                                    }
                                                } elseif ($existingEngine) {
                                                    $fail('Un engin avec ce numéro de chassis existe déjà.');
                                                }
                                            };
                                        },
                                    ]),
                                TextInput::make('moteur')->label('Moteur')->numeric()->required(),

                                TextInput::make('carosserie')->label('Carosserie')->required(),

                                ColorPicker::make('couleur')->label('Couleur')->required(),

                            ]),

                        Grid::make(6)
                            ->schema([

                                TextInput::make('poids_total_en_charge')
                                    ->label('Poids total en charge')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('poids_a_vide')
                                    ->label('Poids à vide')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('poids_total_roulant')
                                    ->label('Poids total roulant')
                                    ->numeric(),

                                TextInput::make('charge_utile')
                                    ->label('Charge à vide')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('largeur')
                                    ->label('Largeur')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('surface')
                                    ->label('Surface')
                                    ->numeric()
                                    ->required(),

                            ]),

                        Select::make('modele_id')
                            ->label('Modèle')
                            ->options(Modele::where('state', StatesClass::Activated()->value)->pluck('nom_modele', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('type_id')
                            ->label("Type d'engin")
                            ->options(Type::where('state', StatesClass::Activated()->value)->pluck('nom_type', 'id'))
                            ->searchable()
                            ->required(),

                        Datepicker::make('date_cert_precedent')
                            ->label('date_cert_precedent'),

                        TextInput::make('numero_carte_grise')
                            ->label('Numéro de la carte grise')
                            ->required()
                            ->rules([
                                function ($record) {
                                    return function (string $attribute, $value, Closure $fail) use ($record) {

                                        $existingEngine = Engine::where('numero_carte_grise', $value)->first();

                                        if ($existingEngine && $record) {
                                            if ($existingEngine->id != $record->id) {
                                                $fail('Un engin avec ce numéro de carte grise existe déjà.');
                                            }
                                        } elseif ($existingEngine) {
                                            $fail('Un engin avec ce numéro de carte grise existe déjà.');
                                        }

                                    };
                                },
                            ]),

                        Grid::make(1)
                            ->schema([
                                FileUpload::make('car_document')
                                    ->maxSize(1024)
                                    ->label('Carte grise de l\'engin')
                                    ->disk("medias")
                            ->directory("cartes")
                                    ->enableDownload()
                                    ->enableOpen()
                                    ->required(),

                                // Grid::make(2)
                                //     ->schema([

                                //         Placeholder::make('Departement')
                                //             ->label('Département')
                                //             ->content(function (?Engin $record): string {

                                //                 if ($record) {
                                //                     $chauffeur = Chauffeur::where('id', $record->chauffeur_id)->first();

                                //                     if ($chauffeur) {
                                //                         return Departement::where('id', $chauffeur->departement_id)->value('nom_departement');
                                //                     } else
                                //                         return 'Aucun département affecté';
                                //                 } else
                                //                     return null;

                                //             })->hidden(fn(?Engin $record) => $record === null),

                                //         Placeholder::make('Chauffeur')
                                //             ->label('Chauffeur')
                                //             // ->content(fn(?Engin $record):  ?string => Chauffeur::where('id', $record->chauffeur_id)->value('name')),
                                //             ->content(function (?Engin $record): string {

                                //                 if ($record) {
                                //                     $chauffeur = Chauffeur::where('id', $record->chauffeur_id)->first();

                                //                     if ($chauffeur) {
                                //                         return $chauffeur->name;
                                //                     } else
                                //                         return 'Aucun chauffeur affecté';
                                //                 }
                                //                 return null;

                                //             })->hidden(fn(?Engin $record) => $record === null),
                                //     ]),

                            ]),

                        Select::make('carburant_id')
                            ->options(Carburant::where('state', StatesClass::Activated()->value)
                                ->pluck('type_carburant', 'id'))
                            ->label('Carburant')
                            ->searchable()
                            ->required(),

                        Select::make('departement_id')
                            ->label('Centre')
                            ->disabledOn('edit')
                            ->options(Departement::pluck('sigle_centre', 'code_centre'))
                            ->searchable()
                            ->reactive(),

                    ])
                    ->columns(2),

                // Select::make('chauffeur_id')
                //     ->label('Chauffeur')
                //     ->options(function (callable $get) {

                //         return Chauffeur::where('state', StatesClass::Activated())
                //             ->where('id', $get('departement_id'))
                //             ->pluck('name', 'id');
                //     })
                //     ->hiddenOn('view')
                //     ->searchable(),

                Hidden::make('user_id')
                    ->default(auth()->user()->id)
                    ->disabled(),

                Hidden::make('updated_at_user_id')
                    ->default(auth()->user()->id)
                    ->disabled(),

                Hidden::make('assurances_mail_sent')
                    ->default(0),

                Hidden::make('visites_mail_sent')
                    ->default(0),

                    Hidden::make('tvm_mail_sent')
                    ->default(0),

                Hidden::make('state')->default(StatesClass::Activated()->value),

                CommonInfos::PlaceholderCard(),

            ]);

    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([

                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable(query: function (Builder $query, string $search): Builder {

                        return $query->selectRaw('plate_number')->whereRaw('LOWER(plate_number) LIKE ?', ['%'.strtolower($search).'%']);

                    }),

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

                DepartementColumn::make('departement_id')
                    ->label('Centre')
                    ->tooltip(fn ($record) => Departement::find($record->departement_id)->libelle),

                ImageColumn::make('logo')
                    ->label('Marque')
                    ->default(asset('images/default_product_image.jpg'))
                    ->alignment('center'),

                TextColumn::make('date_expiration')
                    ->label('Visite (expiration)')
                    ->dateTime('d-m-Y'),

                TextColumn::make('date_fin')
                    ->label('Assurance (expiration)')
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

            ])
            ->defaultSort('engines.created_at', 'desc')

            ->filters([

                Filter::make('Division')
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['departement_id']) {
                            return null;
                        }

                        return 'Département: '.Departement::where('code_centre', $data['departement_id'])->value('sigle_centre');
                    })
                    ->form([
                        Select::make('departement_id')
                            ->searchable()
                            ->label('Département')
                            ->options(Departement::pluck('sigle_centre', 'code_centre')),

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['departement_id'],
                                function (Builder $query, $status) {
                                    $search = Departement::where('code_centre', $status)->first()->code_centre;

                                    return $query->where('departement_id', $search);
                                }
                            );
                    }),

                Filter::make('Etat')
                    ->form([
                        Select::make('etat')
                            ->searchable()
                            ->label('Etat')
                            ->options([
                                StatesClass::Activated()->value =>  'En état',
                                StatesClass::Repairing()->value=> 'En réparation',
                            ])
                            
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['etat'],
                                fn (Builder $query, $status): Builder => $query->where('engines.state', $status),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['etat']) {
                            return null;
                        }
                        else if($data['etat'] == StatesClass::Activated()->value)
                        {
                            return 'Etat: '. StatesClass::Activated()->value;
                        }
                        else return 'Etat: '. StatesClass::Repairing()->value;

                    }),

                Filter::make('type')
                    ->form([
                        Select::make('type_id')
                            ->searchable()
                            ->label('Type d\'engin')
                            ->options(
                                Type::pluck('nom_type', 'id')
                            ),

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['type_id'],
                                fn (Builder $query, $status): Builder => $query->where('engines.type_id', $status),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (! $data['type_id']) {
                            return null;
                        }

                        return 'Type d\'engin: '.Type::where('id', $data['type_id'])->first()->nom_type;
                    }),

            ])
            ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                    // Tables\Actions\DeleteBulkAction::make(),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssurancesRelationManager::class,
            RelationManagers\VisitesRelationManager::class,
            RelationManagers\ReparationsRelationManager::class,
            ConsommationCarburantsRelationManager::class,
            AffectationsRelationManager::class,
            OrdreDeMissionsRelationManager::class,
            TvmsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEngines::route('/'),
            'create' => Pages\CreateEngine::route('/create'),
            'edit' => Pages\EditEngine::route('/{record}/edit'),
            'view' => Pages\ViewEngines::route('/{record}/view'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::engines_read()->value,
            PermissionsClass::engines_update()->value,
            PermissionsClass::engines_create()->value,
        ]);
    }
}
