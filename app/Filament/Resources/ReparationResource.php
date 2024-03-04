<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Division;
use App\Models\Direction;
use App\Models\Reparation;
use App\Models\Prestataire;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Tables\Columns\PrestataireColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ReparationResource\Pages;
use App\Support\Database\ReparationValidationStates;
use Filament\Forms\Components\Builder as FilamentBuilder;

class ReparationResource extends Resource
{
    protected static ?string $model = Reparation::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $modelLabel = 'Maintenance';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments';

    public static function form(Form $form): Form
    {

        return $form

            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Select::make('engine_id')
                                    ->label('Numéro de plaque')
                                    ->options(
                                        Engine::where('engines.state', '<>', StatesClass::Deactivated()->value)
                                            ->pluck('plate_number', 'id')
                                    )
                                    ->searchable()
                                    ->required(),

                                Select::make('prestataire_id')
                                    ->label('Prestataire')
                                    ->options(Prestataire::pluck('raison_social_fr', 'code_fr'))
                                    ->searchable()
                                    ->preload(true)
                                    ->reactive()
                                    ->required(),

                                DatePicker::make('date_lancement')
                                    ->label("Date d'envoi en réparation")
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label('Date de retour du véhicule')
                                    ->afterOrEqual('date_lancement'),

                                Hidden::make('state')->default(StatesClass::Activated()->value),

                                Hidden::make('validation_state')->default(ReparationValidationStates::Declaration_initiale()->value),

                            ])->columns(2),

                        Section::make('Informations du fournisseur')
                            ->description(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('raison_social_fr') : '')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('Raison sociale')
                                            ->content(fn($get) => $get('prestataire_id') && (Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('nom_fr')) ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('nom_fr') : '-'),

                                        Placeholder::make('Adresse')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('adr_fr') : '-'),

                                        Placeholder::make('Contact_1')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('tel_fr') : '-'),

                                        Placeholder::make('Contact_2')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('tel2_frs') : '-'),


                                            Placeholder::make('Secteur d\'activité')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('sect_activ') : '-'),
                                    ]),

                            ]),
                        Section::make('Travaux à faire')
                            ->schema([

                                Select::make('révisions')
                                    ->label('Type de la réparation')
                                    ->relationship('typeReparations', 'libelle', fn(Builder $query) => $query->where('state', StatesClass::Activated()->value))
                                    ->multiple()
                                    ->searchable()
                                    ->preload(true)
                                    ->required(),

                                FilamentBuilder::make('infos')
                                    ->label('Achats')
                                    ->blocks([
                                        FilamentBuilder\Block::make('Achat')
                                            ->icon('heroicon-o-adjustments')
                                            ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                        TextInput::make('Designation'),

                                                        TextInput::make('nombre')
                                                            ->numeric()
                                                            ->minValue(1)
                                                            ->reactive()
                                                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('montant', $state * $get('Prix_unitaire'))),

                                                        TextInput::make('Prix_unitaire')
                                                            ->numeric()
                                                            ->suffix('FCFA')
                                                            ->minValue(1)
                                                            ->reactive()
                                                            ->integer()
                                                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('montant', $state * $get('nombre'))),

                                                        TextInput::make('montant')
                                                            ->suffix('FCFA')
                                                            ->numeric()
                                                            ->integer()
                                                            ->disabled()
                                                            ->dehydrated(true),
                                                    ]),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),

                        Section::make('Suivi budgétaire')
                            ->schema([

                                Radio::make('budget')
                                    ->label("BUDGETS:")
                                    ->options([
                                        'EXPLOITATION' => 'EXPLOITATION',
                                        'INVESTISSEMENTS' => 'INVESTISSEMENTS',
                                    ])->inline(),

                                Grid::make(3)
                                    ->schema([


                                        Fieldset::make('Références du projet')
                                            ->schema([

                                                TextInput::make('ref_projet')
                                                    ->label("Type"),


                                                TextInput::make('num_projet')
                                                    ->label("Numéro"),
                                            ]),

                                        Radio::make('inscription_budget')
                                            ->label("INSCRIPTION AU BUDGET:")
                                            ->options([
                                                true => 'OUI',
                                                false => 'NON',
                                            ])->inline(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('compte_imputation')
                                            ->label("Compte d'imputation"),

                                        TextInput::make('libelle')
                                            ->label('Libelle'),

                                        TextInput::make('dispo_pro')
                                            ->label("Disponibilité provisoire"),

                                        TextInput::make('dispo_pro_apres_engagement')
                                            ->label('Disponibilité provisoire après engagement'),

                                            TextInput::make('compte_four')
                                            ->label("Compte fournisseur")
                                            ->columnSpanFull(),
                                    ])


                            ]),



                    ]),


                TextInput::make('cout_reparation')
                    ->label('Cout total de la révision')
                    ->numeric()
                    ->required(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false)
                    ->visible(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false)
                    ->required(),

                FileUpload::make('facture')
                    ->required(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false)
                    ->visible(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false)
                    ->label('Proforma')
                    ->enableDownload()
                    ->enableOpen(),

                TextInput::make('ref_proforma')
                    ->label('Référence du devis')
                    ->required(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false)
                    ->visible(fn($record) => ($record) && ($record->validation_state == ReparationValidationStates::Demande_de_travail_dg()->value) ? true : false),

                MarkdownEditor::make('details')
                    ->label('Détails')
                    ->disableAllToolbarButtons()
                    ->enableToolbarButtons([
                        // 'bold',
                        // 'bulletList',
                        // 'edit',
                        // 'italic',
                        // 'preview',
                        // 'strike',
                    ])
                    ->columnSpanFull()
                    ->placeholder('Détails de la révision'),

                Hidden::make('user_id')->default(auth()->user()->id),

                Hidden::make('updated_at_user_id')->default(auth()->user()->id),

                CommonInfos::PlaceholderCard(),

            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id'),
                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_lancement')
                    ->label('Date d\'envoi en réparation')
                    ->dateTime('d-m-Y'),

                TextColumn::make('date_fin')
                    ->placeholder('-')
                    ->label('Date de retour du véhicule')
                    ->dateTime('d-m-Y'),

                TagsColumn::make('typeReparations.libelle')
                    ->label('Type de la réparation')
                    ->limit(1)
                    ->searchable(),

                TextColumn::make('validation_state')
                    ->label("Statut de validation")
                    ->color('primary')
                    ->weight('bold')
                    ->description(function (Reparation $record) {

                        $engin = Engine::find($record->engine_id);

                        $division = Division::find($engin->departement_id);

                        $direction = Direction::find($division->direction_id);



                        $returnString = "";

                        switch ($record->validation_state) {

                            case ReparationValidationStates::Declaration_initiale()->value:
                                $returnString = 'En attente de validation du chef ' . $division->sigle_division;
                                break;

                            case ReparationValidationStates::Demande_de_travail_Chef_division()->value:
                                $returnString = 'En attente de validation du  ' . $direction->sigle_direction;
                                break;

                            case ReparationValidationStates::Demande_de_travail_directeur_division()->value:
                                $returnString = 'En attente de validation du DG';
                                break;

                            case ReparationValidationStates::Demande_de_travail_dg()->value:
                                $returnString = 'En attente du proforma';
                                break;

                            case ReparationValidationStates::Demande_de_travail_chef_parc()->value:
                                $returnString = 'En attente de validation par la DIGA';
                                break;

                            case ReparationValidationStates::Demande_de_travail_diga()->value:
                                $returnString = 'En attente de validation du Budget';
                                break;


                        };

                        return $returnString;


                    }),

                // ->colors([
                //     'secondary' => static fn ($state): bool => $state == 'draft',
                //     'warning' => static fn ($state): bool => $state == 'reviewing',
                //     'success' => static fn ($state): bool => $state == 'published',
                //     'danger' => static fn ($state): bool => $state == 'rejected',
                // ])
                // ->icons([
                //     'heroicon-o-x',
                //     'heroicon-o-document' => 'draft',
                //     'heroicon-o-refresh' => 'reviewing',
                //     'heroicon-o-truck' => 'published',
                // ]),

                PrestataireColumn::make('prestataire')
                    ->label('Prestataire'),

                // TextColumn::make('cout_reparation')
                //     ->placeholder('-')
                //     ->label('Coût de la réparation'),

            ])->defaultSort('reparations.created_at', 'desc')
            ->filters([
                Filter::make('date_lancement')
                    ->label('Date d\'envoi en réparation')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('date_lancement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_lancement', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date d\'envoi en réparation:  ' . Carbon::parse($data['date_from'])->format('d-m-Y') . ' au ' . Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),

                // SelectFilter::make('Prestataire')
                //     ->multiple()
                //     ->relationship('prestataire', 'nom'),

                Filter::make('Prestataire')
                    ->form([
                        Select::make('prestataire_id')
                            ->searchable()
                            ->label('Prestataire')
                            ->options(Prestataire::pluck('raison_social_fr', 'code_fr')),

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['prestataire_id'],
                                function (Builder $query, $status) {
                                    $search = Prestataire::where('code_fr', $status)->value('code_fr');

                                    return $query->where('prestataire_id', $search);
                                }
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['prestataire_id']) {
                            return null;
                        }

                        return 'Prestataire: ' . Prestataire::where('code_fr', $data['prestataire_id'])->value('raison_social_fr');
                    }),

                SelectFilter::make('Type de la réparation')
                    ->multiple()
                    ->relationship('typeReparations', 'libelle'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReparations::route('/'),
            'create' => Pages\CreateReparation::route('/create'),
            'edit' => Pages\EditReparation::route('/{record}/edit'),
            'view' => Pages\ViewReparation::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::reparation_read()->value,
            PermissionsClass::reparation_update()->value,
        ]);
    }
}
