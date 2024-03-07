<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Circuit;
use App\Models\Division;
use App\Models\Direction;
use App\Models\Reparation;
use App\Models\Prestataire;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Support\Database\RolesEnum;
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


                                Select::make('circuit_id')
                                    ->label('Circuit de validation')
                                    ->options(
                                        Circuit::pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->required(),


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

                                Hidden::make('validation_state')->default(""),

                                Hidden::make("validation_step")->default(0),

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

                        // Section::make('Suivi budgétaire')
                        //     ->schema([

                        //         // Radio::make('budget')
                        //         //     ->label("BUDGETS:")
                        //         //     ->options([
                        //         //         'EXPLOITATION' => 'EXPLOITATION',
                        //         //         'INVESTISSEMENTS' => 'INVESTISSEMENTS',
                        //         //     ])->inline()

                        //         //     ->required(function ($record) {

                        //         //         if (
                        //         //             ($record) && (in_array($record->validation_state, [
                        //         //                 ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                 ReparationValidationStates::Termine()->value

                        //         //             ]))
                        //         //         ) {
                        //         //             return true;
                        //         //         } else
                        //         //             return false;
                        //         //     }),

                        //         // Grid::make(3)
                        //         //     ->schema([

                        //         //         Fieldset::make('Références du projet')
                        //         //             ->schema([

                        //         //                 TextInput::make('ref_projet')
                        //         //                     ->label("Type")
                        //         //                     ->required(function ($record) {

                        //         //                         if (
                        //         //                             ($record) && (in_array($record->validation_state, [
                        //         //                                 ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                                 ReparationValidationStates::Termine()->value

                        //         //                             ]))
                        //         //                         ) {
                        //         //                             return true;
                        //         //                         } else
                        //         //                             return false;
                        //         //                     }),


                        //         //                 TextInput::make('num_projet')
                        //         //                     ->label("Numéro")
                        //         //                     ->required(function ($record) {

                        //         //                         if (
                        //         //                             ($record) && (in_array($record->validation_state, [
                        //         //                                 ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                                 ReparationValidationStates::Termine()->value

                        //         //                             ]))
                        //         //                         ) {
                        //         //                             return true;
                        //         //                         } else
                        //         //                             return false;
                        //         //                     }),
                        //         //             ]),

                        //         //         Radio::make('inscription_budget')
                        //         //             ->label("INSCRIPTION AU BUDGET:")
                        //         //             ->options([
                        //         //                 true => 'OUI',
                        //         //                 false => 'NON',
                        //         //             ])->inline()
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             }),
                        //         //     ]),

                        //         // Grid::make(2)
                        //         //     ->schema([
                        //         //         TextInput::make('compte_imputation')
                        //         //             ->label("Compte d'imputation")
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             }),

                        //         //         TextInput::make('libelle')
                        //         //             ->label('Libelle')
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             }),

                        //         //         TextInput::make('dispo_pro')
                        //         //             ->label("Disponibilité provisoire")
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             }),

                        //         //         TextInput::make('dispo_pro_apres_engagement')
                        //         //             ->label('Disponibilité provisoire après engagement')
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             }),

                        //         //         TextInput::make('compte_four')
                        //         //             ->label("Compte fournisseur")
                        //         //             ->columnSpanFull()
                        //         //             ->required(function ($record) {

                        //         //                 if (
                        //         //                     ($record) && (in_array($record->validation_state, [
                        //         //                         ReparationValidationStates::Demande_de_travail_diga()->value,
                        //         //                         ReparationValidationStates::Termine()->value

                        //         //                     ]))
                        //         //                 ) {
                        //         //                     return true;
                        //         //                 } else
                        //         //                     return false;
                        //         //             })
                        //         //     ])


                        //     ])->visible(function ($record) {

                        //         if (
                        //             ($record) && (in_array($record->validation_state, [
                        //                 ReparationValidationStates::Bon_de_travail_chef_parc()->value,
                        //                 //et les autres
                        //                 ReparationValidationStates::Termine()->value

                        //             ]))
                        //         ) {
                        //             return true;
                        //         } else
                        //             return false;
                        //     }),



                    ]),
              Section::make("Devis")
              ->schema([
                FileUpload::make('facture')
                ->required(function ($record) {
                    if ($record) {

                        $circuit = Circuit::find($record->circuit_id)->value("steps");

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
        
                        $arrayKeys = array_keys($roleIds);

                        $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);

                        if (in_array($record->validation_step, $indicesDesired)) {
                            return true;
                        } else
                            return false;
                    } else
                        return false;


                })
                ->visible(function ($record) {
                    if ($record) {

                        if($record->validation_state == "nextValue")
                        {
                            return true;
                        }
                        else{

                            $circuit = Circuit::find($record->circuit_id)->value("steps");

                            foreach ($circuit as $key => $item) {

                                $roleIds[] = $item['role_id'];
                            }

                            $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
            
                            $arrayKeys = array_keys($roleIds);

                            $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                 
                            if (in_array($record->validation_step, $indicesDesired)) {
                                return true;
                            } else
                                return false;
                        }

                       
                    } else
                        return false;


                })->label('Proforma')
                ->enableDownload()
                ->enableOpen(),

            FileUpload::make('bon_commande')
            ->dehydrated(false)
                ->label("Bon de commande")
                ->required(function ($record) {
                    if ($record) {

                        $circuit = Circuit::find($record->circuit_id)->value("steps");

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
        
                        $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                        $secondOccurenceOfRole = array_search($searchedRoleId, $slicedArray); // first array key where role occurs

                        $arrayKeys = array_keys($slicedArray);

                        $indicesDesired = array_slice($arrayKeys, $secondOccurenceOfRole + 1); // key to slice array from
        




                        if (in_array($record->validation_step, $indicesDesired)) {
                            return true;
                        } else
                            return false;
                    } else
                        return false;


                })
                ->visible(function ($record) {
                    if ($record) {

                        $circuit = Circuit::find($record->circuit_id)->value("steps");

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs


                        
        
                        $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                        $secondOccurenceOfRole = array_search($searchedRoleId, $slicedArray);


                        $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) +  $firstOccurenceOfRole +1;



                        $remainingKeys = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray);

                        // array_flip(($remainingKeys));

                        // dd(array_flip(($remainingKeys) ));

                        $arrayKeys = array_keys($slicedArray);

                        $indicesDesired = array_slice($slicedArray, $secondOccurenceOfRole +1); // key to slice array from


                        $r =  array_keys($indicesDesired);


        // dd( $roleIds, $firstOccurenceOfRole,  $slicedArray,   $indicesDesired, in_array($record->validation_step, $indicesDesired), $r);

                        if (in_array($record->validation_step, $remainingKeys)) {
                            return true;
                        } else
                            return false;
                    } else
                        return false;

                })
                ->enableDownload()
                ->enableOpen(),

                Grid::make(2)
                ->schema([
                    TextInput::make('cout_reparation')
                    ->label('Cout total de la révision')
                    ->numeric()
                    ->required(function ($record) {
    
                        if ($record) {
    
                            $circuit = Circuit::find($record->circuit_id)->value("steps");
    
                            foreach ($circuit as $key => $item) {
    
                                $roleIds[] = $item['role_id'];
                            }
    
                            $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;
    
                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
            
                            $arrayKeys = array_keys($roleIds);
    
                            $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);
    
                            if (in_array($record->validation_step, $indicesDesired)) {
                                return true;
                            } else
                                return false;
                        } else
                            return false;
    
    
                    })
                    ->visible(
                        function ($record) {
    
                            if ($record) {
    
                                $circuit = Circuit::find($record->circuit_id)->value("steps");
    
                                foreach ($circuit as $key => $item) {
    
                                    $roleIds[] = $item['role_id'];
                                }
    
                                $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;
    
                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                $arrayKeys = array_keys($roleIds);
    
                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);
    
                                if (in_array($record->validation_step, $indicesDesired)) {
                                    return true;
                                } else
                                    return false;
                            } else
                                return false;
    
                        }
                    ),
    
    
    
                TextInput::make('ref_proforma')
                    ->label('Référence du devis')
                    ->required(function ($record) {
                        if ($record) {
    
                            $circuit = Circuit::find($record->circuit_id)->value("steps");
    
                            foreach ($circuit as $key => $item) {
    
                                $roleIds[] = $item['role_id'];
                            }
    
                            $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;
    
                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
            
                            $arrayKeys = array_keys($roleIds);
    
                            $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);
    
                            if (in_array($record->validation_step, $indicesDesired)) {
                                return true;
                            } else
                                return false;
                        } else
                            return false;
    
    
                    })
                    ->visible(function ($record) {
                        if ($record) {
    
                            $circuit = Circuit::find($record->circuit_id)->value("steps");
    
                            foreach ($circuit as $key => $item) {
    
                                $roleIds[] = $item['role_id'];
                            }
    
                            $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;
    
                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
            
                            $arrayKeys = array_keys($roleIds);
    
                            $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1);
    
                            if (in_array($record->validation_step, $indicesDesired)) {
                                return true;
                            } else
                                return false;
                        } else
                            return false;
    
    
                    }),
                ])
           
            ])->visible(function ($record) {
                if ($record) {

                    if($record->validation_state == "nextValue")
                    {
                        return true;
                    }
                    else{

                        $circuit = Circuit::find($record->circuit_id)->value("steps");

                        foreach ($circuit as $key => $item) {

                            $roleIds[] = $item['role_id'];
                        }

                        $searchedRoleId = (Role::where("name", RolesEnum::Directeur_general()->value)->first())->id;

                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
        
                        $arrayKeys = array_keys($roleIds);

                        $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
             
                        if (in_array($record->validation_step, $indicesDesired)) {
                            return true;
                        } else
                            return false;
                    }

                   
                } else
                    return false;


            }),
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
                    ->formatStateUsing(function ($state) {

                        if ($state == "nextValue") {
                            return "Terminée";
                        } else {
                            $validator = (Role::find($state))->name;

                            return "En attente de validation de: " . $validator;
                        }

                    })
                    ->color('primary')
                    ->weight('bold')
                // ->description(function (Reparation $record) {

                //     $engin = Engine::find($record->engine_id);

                //     $division = Division::find($engin->departement_id);

                //     $direction = Direction::find($division->direction_id);



                //     $returnString = "";

                //     switch ($record->validation_state) {

                //         case ReparationValidationStates::Declaration_initiale()->value:
                //             $returnString = 'En attente de validation du chef ' . $division->sigle_division;
                //             break;

                //         case ReparationValidationStates::Demande_de_travail_Chef_division()->value:
                //             $returnString = 'En attente de validation du  ' . $direction->sigle_direction;
                //             break;

                //         case ReparationValidationStates::Demande_de_travail_directeur_division()->value:
                //             $returnString = 'En attente de validation du DG';
                //             break;

                //         case ReparationValidationStates::Demande_de_travail_dg()->value:
                //             $returnString = 'En attente du proforma';
                //             break;

                //         case ReparationValidationStates::Demande_de_travail_chef_parc()->value:
                //             $returnString = 'En attente de validation du devis par la DIGA';
                //             break;

                //         case ReparationValidationStates::Demande_de_travail_diga()->value:
                //             $returnString = 'Mise en place du Bon de travail par Chef Parc';
                //             break;   // ici que je suis au 5 03 2024

                //         case ReparationValidationStates::Bon_de_travail_chef_division()->value:
                //             $returnString = 'Bon validé par le chef Division';
                //             break;

                //         case ReparationValidationStates::Bon_de_travail_chef_parc()->value:
                //             $returnString = 'Suivi du Budget';  //suivi budgétaire des engagements
                //             break;


                //     };

                //     return $returnString;


                // }),

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
                ,
                PrestataireColumn::make('prestataire')
                    ->label('Prestataire')
                ,

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
