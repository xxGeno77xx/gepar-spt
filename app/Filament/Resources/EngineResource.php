<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Type;
use App\Models\User;
use Filament\Tables;
use App\Models\Modele;
use App\Models\Carburant;
use App\Models\Chauffeur;
use App\Models\Departement;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\Engine as Engin;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\EngineResource\Pages;
use App\Filament\Resources\EngineResource\RelationManagers;
use App\Filament\Resources\EngineResource\RelationManagers\ConsommationCarburantsRelationManager;

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
                            ->label("Matricule précédent")
                            ->regex('/^\d{4}-[A-Z]{2}$/')
                            ->placeholder('1234-AB')
                            ->maxLength(7)
                            ->unique(ignoreRecord: true),

                        TextInput::make('plate_number')
                            ->label('Numéro de plaque')
                            ->placeholder('1234-AB')
                            ->regex('/^\d{4}-[A-Z]{2}$/')
                            ->maxLength(7)
                            ->required()
                            ->unique(ignoreRecord: true),

                        DatePicker::make('circularization_date')
                            ->label("Mise en circulation"),

                        DatePicker::make('date_aquisition')
                            ->label("Date d'acquisition"),

                        TextInput::make('price')
                            ->label("Prix d'achat")
                            ->columnSpanFull()
                            ->suffix('FCFA')
                            ->numeric(),

                        TextInput::make('kilometrage_achat')
                            ->label("Kilométrage à l'achat")
                            ->columnSpanFull()
                            ->numeric(),

                        Grid::make(6)
                            ->schema([
                                TextInput::make('power')
                                    ->label("Puissance")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('pl_ass')
                                    ->label("pl_ass")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('numero_chassis')
                                    ->label("Numéro de chassis")
                                    ->unique(ignoreRecord: true)
                                    ->required(),


                                TextInput::make('moteur')
                                    ->label("Moteur")
                                    ->numeric()
                                    ->required(),


                                TextInput::make('carosserie')
                                    ->label("Carosserie")
                                    ->required(),

                                ColorPicker::make('couleur')
                                    ->label("Couleur")
                                    ->required(),

                            ]),

                        Grid::make(6)
                            ->schema([

                                TextInput::make('poids_total_en_charge')
                                    ->label("Poids total en charge")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('poids_a_vide')
                                    ->label("Poids à vide")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('poids_total_roulant')
                                    ->label("Poids total roulant")
                                    ->numeric(),

                                TextInput::make('Charge_utile')
                                    ->label("Charge à vide")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('largeur')
                                    ->label("Largeur")
                                    ->numeric()
                                    ->required(),

                                TextInput::make('surface')
                                    ->label("Surface")
                                    ->numeric()
                                    ->required(),

                            ]),

                        Select::make('modele_id')
                            ->label("Modèle")
                            ->options(Modele::where('state', StatesClass::Activated())->pluck('nom_modele', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('type_id')
                            ->label("Type d'engin")
                            ->options(Type::where('state', StatesClass::Activated())->pluck('nom_type', 'id'))
                            ->searchable()
                            ->required(),

                        Datepicker::make('date_cert_precedent')
                            ->label("date_cert_precedent"),

                        TextInput::make('numero_carte_grise')
                            ->label("Numéro de la carte grise")
                            ->required(),


                        Grid::make(1)
                            ->schema([
                                FileUpload::make('car_document')
                                    ->maxSize(1024)
                                    ->label('Carte grise de l\'engin')
                                    ->image()
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


                        Radio::make('carburant_id')
                            ->options(Carburant::all()
                                ->pluck('type_carburant', 'id'))
                            ->inline()
                            ->label('Carburant')
                            ->required(),

                    ])
                    ->columns(2),

                Select::make('departement_id')
                    ->label('Département')
                    ->options(Departement::where('state', StatesClass::Activated())->pluck('nom_departement', 'id'))
                    ->searchable()
                    // ->dehydrated(false)
                    ->reactive()
                    ->hiddenOn('view'),

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

                    CommonInfos::PlaceholderCard(),

            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([

                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable(),

                // TextColumn::make('chauffeur')
                //     ->label('Chauffeur')
                //     ->searchable()
                //     ->placeholder('-'),

                TextColumn::make('nom_departement')
                    ->label('Département')
                    ->placeholder('-')
                    ->sortable(),

                ImageColumn::make('logo')
                    ->label('Marque')
                    ->default(asset('images/default_product_image.jpg'))
                    ->alignment('center'),

                TextColumn::make('nom_marque')
                    ->alignment('center')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label("Enregistré par")
                    ->alignment('center')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_expiration')
                    ->label("Visite (expiration)")
                    ->searchable()
                    ->dateTime('d-m-Y'),

                TextColumn::make('date_fin')
                    ->label("Assurance (expiration)")
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
                SelectFilter::make('Département')
                    ->multiple()
                    ->relationship('departement', 'nom_departement'),

                Filter::make('Etat')
                    ->form([
                        Select::make('etat')
                            ->searchable()
                            ->label('Etat')
                            ->options([
                                'En état' => StatesClass::Activated()->value,
                                'En Réparation' => StatesClass::Repairing()->value,
                            ])

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['etat'],
                                fn(Builder $query, $status): Builder => $query->where('engines.state', $status),
                            );
                    })
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
            // ConsommationCarburantsRelationManager::class,
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