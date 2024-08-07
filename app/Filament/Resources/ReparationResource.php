<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Circuit;
use App\Models\Division;
use App\Models\Direction;
use App\Models\Reparation;
use App\Models\Departement;
use App\Models\Prestataire;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\DepartementUser;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use App\Functions\ControlFunctions;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Tables\Columns\DepartementColumn;
use App\Tables\Columns\PrestataireColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Placeholder;
use App\Support\Database\AppreciationClass;
use Database\Seeders\RolesPermissionsSeeder;
use App\Filament\Resources\ReparationResource\Pages;
use App\Support\Database\ReparationValidationStates;
use Filament\Forms\Components\Builder as FilamentBuilder;

class ReparationResource extends Resource
{
    protected static ?string $model = Reparation::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments';

    public static function form(Form $form): Form
    {

        return $form

            ->schema([

                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Placeholder::make('motif_rejet')
                                    ->label(new HtmlString('<p style="color: red; font-size: 1.2rem;">Motif du rejet</p>'))
                                    ->content(fn($record) => $record->motif_rejet ? $record->motif_rejet : ''),

                                Placeholder::make('rejete_par')
                                    ->label(new HtmlString('<p style="color: red; font-size: 1.2rem;">Rejeté par</p>'))
                                    ->content(fn($record) => $record->rejete_par ? User::find($record->rejete_par)->name : ''),
                            ]),
                    ])
                    ->visible(fn($record) => $record && $record->motif_rejet ? true : false),

                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([

                                Select::make('engine_id')

                                    ->label('Numéro de plaque')
                                    ->disabled(function ($record) {
                                        if ($record) {
                                            if ($record->validation_step == 0) {
                                                return false;
                                            } else {
                                                return true;
                                            }
                                        }
                                    })
                                    ->options(
                                        function ($record) {
                                            $loggedUser = auth()->user();

                                            if ($record) {
                                                if (
                                                    $loggedUser->hasAnyRole([
                                                        RolesEnum::Directeur_general()->value,
                                                        RolesEnum::Chef_parc()->value,
                                                        RolesEnum::Diga()->value,
                                                        RolesEnum::Dpl()->value,
                                                        RolesEnum::Chef_parc()->value,
                                                        RolesEnum::Budget()->value,
                                                        RolesEnum::Interimaire_DG()->value,
                                                        RolesPermissionsSeeder::SuperAdmin,
                                                    ])
                                                ) {
                                                    return Engine::whereNot('state', StatesClass::Deactivated()->value)->pluck('plate_number', 'id');
                                                } elseif (
                                                    $loggedUser->hasAnyRole([
                                                        RolesEnum::Directeur()->value,
                                                        RolesEnum::Interimaire_Directeur()->value,
                                                        RolesEnum::Delegue_Direction()->value,
                                                        RolesEnum::Directeur_general()->value,
                                                        RolesEnum::Delegue_Direction_Generale()->value,
                                                        RolesEnum::Chef_division()->value,
                                                        RolesEnum::Interimaire_Chef_division()->value,
                                                        RolesEnum::Delegue_Division()->value,
                                                    ])

                                                ) {

                                                    $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->pluck('departement_code_centre')->toArray();

                                                    return Engine::whereIn('engines.departement_id', $userCentresCollection)
                                                        ->whereNot('state', StatesClass::Deactivated()->value)
                                                        ->pluck('plate_number', 'id');
                                                }
                                            } else {

                                                if (
                                                    $loggedUser->hasAnyRole([
                                                        RolesEnum::Directeur()->value,
                                                        RolesEnum::Delegue_Direction()->value,
                                                        RolesEnum::Directeur_general()->value,
                                                        RolesEnum::Delegue_Direction_Generale()->value,
                                                        RolesEnum::Chef_division()->value,
                                                        RolesEnum::Delegue_Division()->value,
                                                    ])
                                                ) {

                                                    return Engine::where('engines.departement_id', $loggedUser->departement_id)
                                                        ->whereNot('state', StatesClass::Deactivated()->value)
                                                        ->pluck('plate_number', 'id');

                                                } elseif (
                                                    $loggedUser->hasAnyRole([
                                                        RolesEnum::Directeur_general()->value,
                                                        RolesEnum::Chef_parc()->value,
                                                        RolesEnum::Diga()->value,
                                                        RolesEnum::Dpl()->value,
                                                        RolesEnum::Chef_parc()->value,
                                                        RolesEnum::Budget()->value,
                                                        RolesEnum::Interimaire_DG()->value,
                                                        RolesPermissionsSeeder::SuperAdmin,

                                                    ])
                                                ) {
                                                    return Engine::whereNot('state', StatesClass::Deactivated()->value)->pluck('plate_number', 'id');
                                                }
                                            }

                                        }

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
                                    ->label('Date de la demande')
                                    ->default(today())
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label('Date de retour du véhicule')
                                    ->afterOrEqual('date_lancement')
                                    ->visible(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    })
                                    ->required(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    }),

                                Select::make('appreciation')
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->options(AppreciationClass::toArray())
                                    ->visible(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    })
                                    ->required(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    }),

                                RichEditor::make('rapport_final')
                                    ->columnSpanFull()
                                    ->label('Rapport de fin de réparation')
                                    ->disableAllToolbarButtons()
                                    ->placeholder('Vos observations concernant la réparation')
                                    ->visible(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    })
                                    ->required(function ($record) {

                                        if ($record) {
                                            $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Chef_parc()->value, 1);

                                            if (in_array($record->validation_step, $remainingSteps)) {
                                                return true;
                                            }

                                            return false;
                                        }
                                    }),

                                Hidden::make('circuit_id')->default(function () {
                                    $userCentresCollection = DepartementUser::where('user_id', auth()->user()->id)->get();

                                    foreach ($userCentresCollection as $userCentre) {
                                        $userCentresIds[] = $userCentre->departement_code_centre;
                                    }

                                    $dirGeneDivisions = [
                                        Departement::where('sigle_centre', 'CI')->first()->code_centre,
                                        Departement::where('sigle_centre', 'DSC')->first()->code_centre,
                                        Departement::where('sigle_centre', 'DSC')->first()->code_centre,
                                    ];

                                    if ((auth()->user()->hasAnyRole([RolesEnum::Chef_Division()->value, RolesEnum::Delegue_Division()->value])) && (array_intersect($userCentresIds, $dirGeneDivisions))) {

                                        return 4; // circuit particulier
                        
                                    } elseif (auth()->user()->hasAnyRole([RolesEnum::Directeur_general()->value, RolesEnum::Delegue_Direction_Generale()->value])) {

                                        return 3; // circuit de  Direction Générale
                        
                                    } elseif (auth()->user()->hasAnyRole([RolesEnum::Directeur()->value, RolesEnum::Delegue_Direction()->value])) {

                                        return 2; // circuit de Direction
                        
                                    } elseif (auth()->user()->hasAnyRole([RolesEnum::Chef_Division()->value, RolesEnum::Delegue_Division()->value])) {

                                        return 1; // circuit de Division
                                    }
                                }),

                                Hidden::make('state')->default(StatesClass::Activated()->value),

                                Hidden::make('validation_state')->default(''),

                                Hidden::make('validation_step')->default(0),

                            ])->columns(2),

                        Section::make('Informations du prestataire')
                            ->description(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('raison_social_fr') : '')
                            ->collapsible()
                            ->schema([
                                Grid::make(3)
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

                                        Placeholder::make('Ville')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('ville_fr') : '-'),

                                        Placeholder::make('Numéro de compte')
                                            ->content(fn($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('numero_compte') : '-'),
                                    ]),

                                Section::make('Devis')
                                    ->schema([
                                        FileUpload::make('facture')
                                            ->disk('public')
                                            ->directory('proforma')
                                            ->required()
                                            ->label('Proforma')
                                            ->enableDownload()
                                            ->enableOpen(),

                                        FileUpload::make('bon_commande')
                                            ->disk('public')
                                            ->directory('bons')
                                            ->label('Bon de commande')
                                            ->required()
                                            ->enableDownload()
                                            ->enableOpen()
                                            ->visible(function ($record) {

                                                if ($record) {

                                                    $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Budget()->value, 2);

                                                    if (in_array($record->validation_step, $remainingSteps)) {
                                                        return true;
                                                    }

                                                    return false;
                                                }

                                            }),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('main_oeuvre')
                                                    ->label('Main d\'oeuvre')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->required(),

                                                TextInput::make('ref_proforma')
                                                    ->label('Référence du devis')
                                                    ->required(),

                                                TextInput::make('cout_reparation')
                                                    ->label('Cout total de la révision')
                                                    ->columnSpanFull()
                                                    ->required()
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),

                        Section::make('Suivi budgétaire des engagements')
                            ->schema([

                                Grid::make(2)
                                    ->schema([
                                        Radio::make("Budgets")
                                            ->label("BUDGETS: ")
                                            ->inline()
                                            ->options([
                                                "Exploitation" => "Exploitation",
                                                "Investissements" => "Investissements",

                                            ]),

                                        TextInput::make("exercice")
                                            ->numeric()
                                            ->minValue(now()->format("Y"))
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        Fieldset::make("Ref_projet")
                                            ->label(strtoupper("references du projet"))
                                            ->schema([

                                                TextInput::make("type")
                                                    ->label("Type "),

                                                TextInput::make("numero")
                                                    ->label("N° "),
                                            ])
                                    ]),

                                Fieldset::make("insc_projet")
                                    ->label(strtoupper("Inscription du projet au budget"))
                                    ->schema([

                                        Radio::make("")
                                            ->inline()
                                            ->options([
                                                "OUI" => "OUI",
                                                "NON" => "NON",
                                            ]),
                                    ]),
                                Fieldset::make("imputation")
                                    ->label(strtoupper("compte d'imputation"))
                                    ->schema([

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make("compte_imputation")
                                                    ->label("Numero de compte ")
                                                    ->reactive()
                                                    ->debounce('1000ms'),

                                                Placeholder::make("libelle")
                                                    ->label("Libellé")
                                                    ->content(function(Callable $get) {

                                                        if($get("compte_imputation"))
                                                        {
                                                            $compte = DB::table("mbudget.fournisseur")->where("numero_compte", $get("compte_imputation"))->first();

                                                            if(!is_null($compte))
                                                            {
                                                                return  $compte->raison_social_fr;
                                                            }
                                                            return new HtmlString("<i>Compte inexistant</i>"); 
                                                        }
                                                       
                                                    }),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make("dispo_prov")
                                                    ->label("Disponibilité provisoire")
                                                        ->numeric(),

                                                TextInput::make("montant")
                                                    ->label("Montant du projet")
                                                        ->numeric(),

                                                TextInput::make("dispo_pro_apres_engag")
                                                    ->label("Disponibilité provisoire après engagement du projet")
                                                    ->numeric(),
                                            ]),

                                    ]),

                                Fieldset::make("fournisseur")
                                    ->label(strtoupper("compte fournisseur"))
                                    ->schema([
                                        TextInput::make("Numero")
                                            ->label("Numero de compte")
                                            ->numeric(),

                                        TextInput::make("libelle")
                                            ->label("Libellé"),
                                    ])


                            ])
                            ->visible(function ($record) {

                                if ($record) {

                                    $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Budget()->value, 1);

                                    if (in_array($record->validation_step, $remainingSteps)) {
                                        return true;
                                    }

                                    return false;
                                }

                            }),


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

                    ]),

                Grid::make(2)->schema([

                    RichEditor::make('avis_diga')
                        ->label('Avis de la DIGA')
                        ->disableAllToolbarButtons()
                        ->placeholder('Observations de la DIGA')
                        ->disabled(function ($record) {

                            if ($record) {

                                if (auth()->user()->hasAnyRole([RolesEnum::Diga()->value])) {
                                    return false;
                                };

                                return true;
                            }

                        })
                        ->visible(function ($record) {

                            if ($record) {

                                $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Diga()->value, 1);

                                if (in_array($record->validation_step, $remainingSteps)) {
                                    return true;
                                }

                                return false;
                            }
                        })
                        ->required(function ($record) {

                            if ($record) {
                                $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Diga()->value, 1);

                                if (in_array($record->validation_step, $remainingSteps)) {
                                    return true;
                                }

                                return false;
                            }
                        }),

                    RichEditor::make('avis_dg')
                        ->label('Avis du Directeur Général / Intérimaire')
                        ->disableAllToolbarButtons()
                        ->placeholder('Observations du Directeur général')
                        ->disabled(function ($record) {

                            if ($record) {

                                if (auth()->user()->hasAnyRole([RolesEnum::Directeur_general()->value, RolesEnum::Interimaire_DG()->value])) {
                                    return false;
                                };

                                return true;
                            }

                        })
                        ->visible(function ($record) {

                            if ($record) {
                                $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Directeur_general()->value, 1);

                                if (in_array($record->validation_step, $remainingSteps)) {
                                    return true;
                                }

                                return false;
                            }
                        })
                        ->required(function ($record) {

                            if ($record) {
                                $remainingSteps = ControlFunctions::getIndicesAfterNthOccurrence($record, RolesEnum::Directeur_general()->value, 1);

                                if (in_array($record->validation_step, $remainingSteps)) {
                                    return true;
                                }

                                return false;
                            }
                        }),

                ]),

                RichEditor::make('details')
                    ->label('Détails')
                    ->disableAllToolbarButtons()
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

                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('engine.departement_id')
                    ->label('Centre')
                    ->formatStateUsing(fn($state) => (DB::table("centre")->where("code_centre", $state)->first()->sigle_centre))
                    ->searchable()
                    ->sortable(),

                // DepartementColumn::make('departement_id')
                // ->label('Centre')
                // ->tooltip(fn ($record) => Departement::find($record->departement_id)->libelle),

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
                    ->label('Statut de validation')
                    ->formatStateUsing(function ($state) {
                        // dd($state);
                        if ($state == 'nextValue') {
                            return 'Terminée';
                        } elseif ($state == ReparationValidationStates::Rejete()->value) {

                            return 'Rejetée';

                        } else {
                            $validator = (Role::find($state))->name;

                            return 'En attente de validation de: ' . $validator;
                        }

                    })
                    ->color(function ($record) {
                        if ($record->validation_state == ReparationValidationStates::Rejete()->value) {
                            return 'danger';
                        } elseif ($record->validation_state == 'nextValue') {
                            return 'success';
                        } else {
                            return 'primary';
                        }
                    })
                    ->weight('bold'),

                PrestataireColumn::make('prestataire')
                    ->label('Prestataire'),

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

                Filter::make('Appreciation')
                    ->form([
                        Select::make('appreciation')
                            ->searchable()
                            ->label('Appréciation')
                            ->options(AppreciationClass::toArray()),

                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['appreciation'],
                                function (Builder $query, $status) use ($data) {
                                    $search = $data['appreciation'];

                                    return $query->where('appreciation', $search);
                                }
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['appreciation']) {
                            return null;
                        } elseif ($data['appreciation'] == AppreciationClass::Insatisfaisant()->value) {
                            return 'Appreciation: ' . AppreciationClass::Insatisfaisant()->value;
                        } else {
                            return 'Appreciation: ' . AppreciationClass::Satisfaisant()->value;
                        }
                    }),

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

    // function getRemainingKeysOfArray(array $array, string $role, int $n)
    // {
    //     $$this->getgetNthOccurrenceOfRequiredRole( $array,  $role,  $n);
    // }
}
