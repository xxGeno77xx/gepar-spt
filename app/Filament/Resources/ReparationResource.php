<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReparationResource\Pages;
use App\Models\Circuit;
use App\Models\Departement;
use App\Models\DepartementUser;
use App\Models\Direction;
use App\Models\Division;
use App\Models\Engine;
use App\Models\Prestataire;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use App\Support\Database\CommonInfos;
use App\Support\Database\PermissionsClass;
use App\Support\Database\ReparationValidationStates;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use App\Tables\Columns\PrestataireColumn;
use Carbon\Carbon;
use Filament\Forms\Components\Builder as FilamentBuilder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
                                    ->content(fn ($record) => $record->motif_rejet ? $record->motif_rejet : ''),

                                Placeholder::make('rejete_par')
                                    ->label(new HtmlString('<p style="color: red; font-size: 1.2rem;">Rejeté par</p>'))
                                    ->content(fn ($record) => $record->rejete_par ? User::find($record->rejete_par)->name : ''),
                            ]),
                    ])
                    ->visible(fn ($record) => $record && $record->motif_rejet ? true : false),

                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([

                                Select::make('engine_id')

                                    ->label('Numéro de plaque')
                                    ->formatStateUsing(function ($record, $state) {
                                        if ($record) {

                                            return Engine::where('id', $state)->first()->plate_number;
                                        }
                                    })
                                    ->dehydrated(fn ($record) => $record ? false : true)
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

                                        // Engine::where('engines.state', '<>', StatesClass::Deactivated()->value)
                                        //                 ->pluck('plate_number', 'id')

                                        function () {
                                            $loggedUser = auth()->user();

                                            if ($loggedUser->hasAnyRole([RolesEnum::Directeur()->value, RolesEnum::Delegue_Direction()->value])) {

                                                $delegues = User::Role(RolesEnum::Delegue_Direction()->value)->get(); // get all delegues de direction

                                                $centesDeTousLesDelegues = [];

                                                $centesDuDirecteur = [];

                                                foreach ($delegues as $delegue) {
                                                    $intermediateArray = DepartementUser::where('user_id', $delegue->id)->get();

                                                    foreach ($intermediateArray as $collection) {
                                                        $centesDeTousLesDelegues[] = $collection->departement_code_centre;
                                                    }
                                                }

                                                // find directors departement number

                                                $centresDuDirecteur = DepartementUser::where('user_id', auth()->user()->id)->get();

                                                foreach ($centresDuDirecteur as $centre) {
                                                    $centesDuDirecteur[] = $centre->departement_code_centre;
                                                }

                                                $intersection = array_intersect($centesDuDirecteur, $centesDeTousLesDelegues);

                                                if ($intersection) {
                                                    $query = Engine::where('departement_id', $intersection[0])
                                                        ->where('engines.state', '<>', StatesClass::Deactivated()->value)
                                                        ->pluck('plate_number', 'id');
                                                } else {
                                                    $query = null;
                                                }

                                                return $query;

                                            } elseif ($loggedUser->hasAnyRole([RolesEnum::Directeur_general()->value, RolesEnum::Delegue_Direction_Generale()->value])) {
                                                $delegues = User::Role(RolesEnum::Delegue_Direction_Generale()->value)->get(); // get all delegues de direction

                                                $centesDeTousLesDelegues = [];

                                                $centesDuDirecteur = [];

                                                foreach ($delegues as $delegue) {
                                                    $intermediateArray = DepartementUser::where('user_id', $delegue->id)->get();

                                                    foreach ($intermediateArray as $collection) {
                                                        $centesDeTousLesDelegues[] = $collection->departement_code_centre;
                                                    }
                                                }

                                                // find directors departement number

                                                $centresDuDirecteur = DepartementUser::where('user_id', auth()->user()->id)->get();

                                                foreach ($centresDuDirecteur as $centre) {
                                                    $centesDuDirecteur[] = $centre->departement_code_centre;
                                                }

                                                $intersection = array_intersect($centesDuDirecteur, $centesDeTousLesDelegues);

                                                if ($intersection) {
                                                    $query = Engine::where('departement_id', $intersection[0])
                                                        ->where('engines.state', '<>', StatesClass::Deactivated()->value)
                                                        ->pluck('plate_number', 'id');
                                                } else {
                                                    $query = null;
                                                }

                                                return $query;

                                            } elseif ($loggedUser->hasAnyRole([RolesEnum::Chef_division()->value, RolesEnum::Delegue_Division()->value])) {

                                                $delegues = User::Role(RolesEnum::Delegue_Division()->value)->get(); // get all delegues de direction

                                                $centesDeTousLesDelegues = [];

                                                $centesDuDirecteur = []; // centre du chef division in this case

                                                foreach ($delegues as $delegue) {
                                                    $intermediateArray = DepartementUser::where('user_id', $delegue->id)->get();

                                                    foreach ($intermediateArray as $collection) {
                                                        $centesDeTousLesDelegues[] = $collection->departement_code_centre;
                                                    }
                                                }

                                                // find chef div departement number

                                                $centresDuDirecteur = DepartementUser::where('user_id', auth()->user()->id)->get();

                                                foreach ($centresDuDirecteur as $centre) {
                                                    $centesDuDirecteur[] = $centre->departement_code_centre;
                                                }

                                                $intersection = array_intersect($centesDuDirecteur, $centesDeTousLesDelegues);

                                                if ($intersection) {
                                                    $query = Engine::where('departement_id', $intersection[0])
                                                        ->where('engines.state', '<>', StatesClass::Deactivated()->value)
                                                        ->pluck('plate_number', 'id');
                                                } else {
                                                    $query = null;
                                                }

                                                return $query;

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
                                    ->required(function ($record) {
                                        if ($record) {

                                            if ($record->validation_state == 'nextValue') {
                                                return true;
                                            } else {

                                                $circuit = Circuit::find($record->circuit_id)->steps;

                                                foreach ($circuit as $key => $item) {

                                                    $roleIds[] = $item['role_id'];
                                                }

                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                                $arrayKeys = array_keys($roleIds);

                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                    return true;
                                                } else {
                                                    return false;
                                                }
                                            }

                                        } else {
                                            return false;
                                        }

                                    })
                                    ->visible(function ($record) {
                                        if ($record) {

                                            if ($record->validation_state == 'nextValue') {
                                                return true;
                                            } else {

                                                $circuit = Circuit::find($record->circuit_id)->steps;

                                                foreach ($circuit as $key => $item) {

                                                    $roleIds[] = $item['role_id'];
                                                }

                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                                $arrayKeys = array_keys($roleIds);

                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                    return true;
                                                } else {
                                                    return false;
                                                }
                                            }

                                        } else {
                                            return false;
                                        }

                                    }),

                                DatePicker::make('date_lancement')
                                    ->label("Date d'envoi en réparation")
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label('Date de retour du véhicule')
                                    ->afterOrEqual('date_lancement')
                                    ->visible(function ($record) {

                                        if ($record) {

                                            $user = auth()->user();

                                            $circuit = Circuit::where('id', $record->circuit_id)->value('steps');

                                            foreach ($circuit as $key => $item) {

                                                $roleIds[] = $item['role_id'];
                                            }

                                            $searchedRoleId = (Role::where('name', RolesEnum::Chef_parc()->value)->first())->id;

                                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                            $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                                            $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                                            $secondSlicedArray = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1);

                                            $thirdOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $secondSlicedArray)) + $secondOccurenceOfRoleInOriginalRolesArray + 1;

                                            $arrayDivided = array_chunk($roleIds, $thirdOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role

                                            if (in_array($record->validation_step, [array_key_last($roleIds), 100])) {

                                                return true;

                                            } else {
                                                return false;
                                            }
                                        }

                                    })
                                    ->required(function ($record) {

                                        if ($record) {

                                            $user = auth()->user();

                                            $circuit = Circuit::where('id', $record->circuit_id)->value('steps');

                                            foreach ($circuit as $key => $item) {

                                                $roleIds[] = $item['role_id'];
                                            }

                                            $searchedRoleId = (Role::where('name', RolesEnum::Chef_parc()->value)->first())->id;

                                            $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                            $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                                            $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                                            $secondSlicedArray = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1);

                                            $thirdOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $secondSlicedArray)) + $secondOccurenceOfRoleInOriginalRolesArray + 1;

                                            $arrayDivided = array_chunk($roleIds, $thirdOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role

                                            if (in_array($record->validation_step, [array_key_last($roleIds), 100])) {

                                                return true;

                                            } else {
                                                return false;
                                            }
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
                            ->description(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('raison_social_fr') : '')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('Raison sociale')
                                            ->content(fn ($get) => $get('prestataire_id') && (Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('nom_fr')) ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('nom_fr') : '-'),

                                        Placeholder::make('Adresse')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('adr_fr') : '-'),

                                        Placeholder::make('Contact_1')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('tel_fr') : '-'),

                                        Placeholder::make('Contact_2')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('tel2_frs') : '-'),

                                        Placeholder::make('Secteur d\'activité')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('sect_activ') : '-'),

                                        Placeholder::make('Ville')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('ville_fr') : '-'),

                                        Placeholder::make('Numéro de compte')
                                            ->content(fn ($get) => $get('prestataire_id') ? Prestataire::where('code_fr', '=', $get('prestataire_id'))->get()->value('numero_compte') : '-'),
                                    ]),

                                    Section::make('Devis')
                                    ->schema([
                                        FileUpload::make('facture')
                                            ->required(function ($record) {
                                                if ($record) {
                
                                                    if ($record->validation_state == 'nextValue') {
                                                        return true;
                                                    } else {
                
                                                        $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                        foreach ($circuit as $key => $item) {
                
                                                            $roleIds[] = $item['role_id'];
                                                        }
                
                                                        $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                        $arrayKeys = array_keys($roleIds);
                
                                                        $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                        if (in_array($record->validation_step, $indicesDesired)) {
                                                            return true;
                                                        } else {
                                                            return false;
                                                        }
                                                    }
                
                                                } else {
                                                    return false;
                                                }
                
                                            })
                                            ->visible(function ($record) {
                                                if ($record) {
                
                                                    if ($record->validation_state == 'nextValue') {
                                                        return true;
                                                    } else {
                
                                                        $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                        foreach ($circuit as $key => $item) {
                
                                                            $roleIds[] = $item['role_id'];
                                                        }
                
                                                        $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                        $arrayKeys = array_keys($roleIds);
                
                                                        $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                        if (in_array($record->validation_step, $indicesDesired)) {
                                                            return true;
                                                        } else {
                                                            return false;
                                                        }
                                                    }
                
                                                } else {
                                                    return false;
                                                }
                
                                            })->label('Proforma')
                                            ->enableDownload()
                                            ->enableOpen(),
                
                                        FileUpload::make('bon_commande')
                                            ->label('Bon de commande')
                                            ->required(function ($record) {
                                                if ($record) {
                
                                                    if ($record->validation_state == 'nextValue') {
                                                        return true;
                                                    } else {
                
                                                        $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                        foreach ($circuit as $key => $item) {
                
                                                            $roleIds[] = $item['role_id'];
                                                        }
                
                                                        $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                        $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);
                
                                                        $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;
                
                                                        $arrayDivided = array_chunk($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role
                
                                                        $ArrayToUse = array_flip($arrayDivided[1]);  //flip array to get keys
                
                                                        if (in_array($record->validation_step, $ArrayToUse)) {
                                                            return true;
                                                        } else {
                                                            return false;
                                                        }
                                                    }
                                                } else {
                                                    return false;
                                                }
                
                                            })
                                            ->visible(function ($record) {
                                                if ($record) {
                
                                                    if ($record->validation_state == 'nextValue') {
                                                        return true;
                                                    } else {
                
                                                        $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                        foreach ($circuit as $key => $item) {
                
                                                            $roleIds[] = $item['role_id'];
                                                        }
                
                                                        $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                        $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);
                
                                                        $secondOccurenceOfRole = array_search($searchedRoleId, $slicedArray);
                
                                                        $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;
                
                                                        // $remainingKeys = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray);
                
                                                        // $arrayKeys = array_keys($slicedArray);
                                                        //
                                                        // $indicesDesired = array_slice($slicedArray, $secondOccurenceOfRole ); // key to slice array from
                
                                                        // $originalRolesIdsKeys = array_keys($roleIds);
                
                                                        $arrayDivided = array_chunk($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role
                
                                                        $ArrayToUse = array_flip($arrayDivided[1]);  //flip array to get keys
                
                                                        if (in_array($record->validation_step, $ArrayToUse)) {
                                                            return true;
                                                        } else {
                                                            return false;
                                                        }
                                                    }
                                                } else {
                                                    return false;
                                                }
                
                                            })
                                            ->enableDownload()
                                            ->enableOpen(),
                
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('cout_reparation')
                                                    ->label('Cout total de la révision')
                                                    ->numeric()
                                                    ->minValue(0)->required(function ($record) {
                                                        if ($record) {
                
                                                            if ($record->validation_state == 'nextValue') {
                                                                return true;
                                                            } else {
                
                                                                $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                                foreach ($circuit as $key => $item) {
                
                                                                    $roleIds[] = $item['role_id'];
                                                                }
                
                                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                                $arrayKeys = array_keys($roleIds);
                
                                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                                    return true;
                                                                } else {
                                                                    return false;
                                                                }
                                                            }
                
                                                        } else {
                                                            return false;
                                                        }
                
                                                    })
                                                    ->visible(function ($record) {
                                                        if ($record) {
                
                                                            if ($record->validation_state == 'nextValue') {
                                                                return true;
                                                            } else {
                
                                                                $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                                foreach ($circuit as $key => $item) {
                
                                                                    $roleIds[] = $item['role_id'];
                                                                }
                
                                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                                $arrayKeys = array_keys($roleIds);
                
                                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                                    return true;
                                                                } else {
                                                                    return false;
                                                                }
                                                            }
                
                                                        } else {
                                                            return false;
                                                        }
                
                                                    }),
                                                    
                
                                                TextInput::make('ref_proforma')
                                                    ->label('Référence du devis')
                                                    ->required(function ($record) {
                                                        if ($record) {
                
                                                            if ($record->validation_state == 'nextValue') {
                                                                return true;
                                                            } else {
                
                                                                $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                                foreach ($circuit as $key => $item) {
                
                                                                    $roleIds[] = $item['role_id'];
                                                                }
                
                                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                                $arrayKeys = array_keys($roleIds);
                
                                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                                    return true;
                                                                } else {
                                                                    return false;
                                                                }
                                                            }
                
                                                        } else {
                                                            return false;
                                                        }
                
                                                    })
                                                    ->visible(function ($record) {
                                                        if ($record) {
                
                                                            if ($record->validation_state == 'nextValue') {
                                                                return true;
                                                            } else {
                
                                                                $circuit = Circuit::find($record->circuit_id)->steps;
                
                                                                foreach ($circuit as $key => $item) {
                
                                                                    $roleIds[] = $item['role_id'];
                                                                }
                
                                                                $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;
                
                                                                $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs
                
                                                                $arrayKeys = array_keys($roleIds);
                
                                                                $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices
                
                                                                if (in_array($record->validation_step, $indicesDesired)) {
                                                                    return true;
                                                                } else {
                                                                    return false;
                                                                }
                                                            }
                
                                                        } else {
                                                            return false;
                                                        }
                
                                                    }),
                                            ]),
                
                                    ])
                                    
                                    

                            ])
                            ->visible(function ($record) {
                                if ($record) {

                                    if ($record->validation_state == 'nextValue') {
                                        return true;
                                    } else {

                                        $circuit = Circuit::find($record->circuit_id)->steps;

                                        foreach ($circuit as $key => $item) {

                                            $roleIds[] = $item['role_id'];
                                        }

                                        $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                                        $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                                        $arrayKeys = array_keys($roleIds);

                                        $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                                        if (in_array($record->validation_step, $indicesDesired)) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    }

                                } else {
                                    return false;
                                }

                            }),
                        Section::make('Travaux à faire')
                            ->schema([

                                Select::make('révisions')
                                    ->label('Type de la réparation')
                                    ->relationship('typeReparations', 'libelle', fn (Builder $query) => $query->where('state', StatesClass::Activated()->value))
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
                                                            ->afterStateUpdated(fn ($state, callable $set, $get) => $set('montant', $state * $get('Prix_unitaire'))),

                                                        TextInput::make('Prix_unitaire')
                                                            ->numeric()
                                                            ->suffix('FCFA')
                                                            ->minValue(1)
                                                            ->reactive()
                                                            ->integer()
                                                            ->afterStateUpdated(fn ($state, callable $set, $get) => $set('montant', $state * $get('nombre'))),

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
                // Section::make('Devis')
                //     ->schema([
                //         FileUpload::make('facture')
                //             ->required(function ($record) {
                //                 if ($record) {

                //                     if ($record->validation_state == 'nextValue') {
                //                         return true;
                //                     } else {

                //                         $circuit = Circuit::find($record->circuit_id)->steps;

                //                         foreach ($circuit as $key => $item) {

                //                             $roleIds[] = $item['role_id'];
                //                         }

                //                         $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                         $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                         $arrayKeys = array_keys($roleIds);

                //                         $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                         if (in_array($record->validation_step, $indicesDesired)) {
                //                             return true;
                //                         } else {
                //                             return false;
                //                         }
                //                     }

                //                 } else {
                //                     return false;
                //                 }

                //             })
                //             ->visible(function ($record) {
                //                 if ($record) {

                //                     if ($record->validation_state == 'nextValue') {
                //                         return true;
                //                     } else {

                //                         $circuit = Circuit::find($record->circuit_id)->steps;

                //                         foreach ($circuit as $key => $item) {

                //                             $roleIds[] = $item['role_id'];
                //                         }

                //                         $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                         $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                         $arrayKeys = array_keys($roleIds);

                //                         $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                         if (in_array($record->validation_step, $indicesDesired)) {
                //                             return true;
                //                         } else {
                //                             return false;
                //                         }
                //                     }

                //                 } else {
                //                     return false;
                //                 }

                //             })->label('Proforma')
                //             ->enableDownload()
                //             ->enableOpen(),

                //         FileUpload::make('bon_commande')
                //             ->label('Bon de commande')
                //             ->required(function ($record) {
                //                 if ($record) {

                //                     if ($record->validation_state == 'nextValue') {
                //                         return true;
                //                     } else {

                //                         $circuit = Circuit::find($record->circuit_id)->steps;

                //                         foreach ($circuit as $key => $item) {

                //                             $roleIds[] = $item['role_id'];
                //                         }

                //                         $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                         $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                         $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                //                         $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                //                         $arrayDivided = array_chunk($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role

                //                         $ArrayToUse = array_flip($arrayDivided[1]);  //flip array to get keys

                //                         if (in_array($record->validation_step, $ArrayToUse)) {
                //                             return true;
                //                         } else {
                //                             return false;
                //                         }
                //                     }
                //                 } else {
                //                     return false;
                //                 }

                //             })
                //             ->visible(function ($record) {
                //                 if ($record) {

                //                     if ($record->validation_state == 'nextValue') {
                //                         return true;
                //                     } else {

                //                         $circuit = Circuit::find($record->circuit_id)->steps;

                //                         foreach ($circuit as $key => $item) {

                //                             $roleIds[] = $item['role_id'];
                //                         }

                //                         $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                         $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                         $slicedArray = array_slice($roleIds, $firstOccurenceOfRole + 1);

                //                         $secondOccurenceOfRole = array_search($searchedRoleId, $slicedArray);

                //                         $secondOccurenceOfRoleInOriginalRolesArray = (array_search($searchedRoleId, $slicedArray)) + $firstOccurenceOfRole + 1;

                //                         // $remainingKeys = array_slice($roleIds, $secondOccurenceOfRoleInOriginalRolesArray);

                //                         // $arrayKeys = array_keys($slicedArray);
                //                         //
                //                         // $indicesDesired = array_slice($slicedArray, $secondOccurenceOfRole ); // key to slice array from

                //                         // $originalRolesIdsKeys = array_keys($roleIds);

                //                         $arrayDivided = array_chunk($roleIds, $secondOccurenceOfRoleInOriginalRolesArray + 1, true); //  cut form second match of dg role

                //                         $ArrayToUse = array_flip($arrayDivided[1]);  //flip array to get keys

                //                         if (in_array($record->validation_step, $ArrayToUse)) {
                //                             return true;
                //                         } else {
                //                             return false;
                //                         }
                //                     }
                //                 } else {
                //                     return false;
                //                 }

                //             })
                //             ->enableDownload()
                //             ->enableOpen(),

                //         Grid::make(2)
                //             ->schema([
                //                 TextInput::make('cout_reparation')
                //                     ->label('Cout total de la révision')
                //                     ->numeric()
                //                     ->minValue(0)
                //                     ->required(function ($record) {
                //                         if ($record) {

                //                             if ($record->validation_state == 'nextValue') {
                //                                 return true;
                //                             } else {

                //                                 $circuit = Circuit::find($record->circuit_id)->steps;

                //                                 foreach ($circuit as $key => $item) {

                //                                     $roleIds[] = $item['role_id'];
                //                                 }

                //                                 $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                                 $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                                 $arrayKeys = array_keys($roleIds);

                //                                 $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                                 if (in_array($record->validation_step, $indicesDesired)) {
                //                                     return true;
                //                                 } else {
                //                                     return false;
                //                                 }
                //                             }

                //                         } else {
                //                             return false;
                //                         }

                //                     })
                //                     ->visible(function ($record) {
                //                         if ($record) {

                //                             if ($record->validation_state == 'nextValue') {
                //                                 return true;
                //                             } else {

                //                                 $circuit = Circuit::find($record->circuit_id)->steps;

                //                                 foreach ($circuit as $key => $item) {

                //                                     $roleIds[] = $item['role_id'];
                //                                 }

                //                                 $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                                 $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                                 $arrayKeys = array_keys($roleIds);

                //                                 $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                                 if (in_array($record->validation_step, $indicesDesired)) {
                //                                     return true;
                //                                 } else {
                //                                     return false;
                //                                 }
                //                             }

                //                         } else {
                //                             return false;
                //                         }

                //                     }),

                //                 TextInput::make('ref_proforma')
                //                     ->label('Référence du devis')
                //                     ->required(function ($record) {
                //                         if ($record) {

                //                             if ($record->validation_state == 'nextValue') {
                //                                 return true;
                //                             } else {

                //                                 $circuit = Circuit::find($record->circuit_id)->steps;

                //                                 foreach ($circuit as $key => $item) {

                //                                     $roleIds[] = $item['role_id'];
                //                                 }

                //                                 $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                                 $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                                 $arrayKeys = array_keys($roleIds);

                //                                 $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                                 if (in_array($record->validation_step, $indicesDesired)) {
                //                                     return true;
                //                                 } else {
                //                                     return false;
                //                                 }
                //                             }

                //                         } else {
                //                             return false;
                //                         }

                //                     })
                //                     ->visible(function ($record) {
                //                         if ($record) {

                //                             if ($record->validation_state == 'nextValue') {
                //                                 return true;
                //                             } else {

                //                                 $circuit = Circuit::find($record->circuit_id)->steps;

                //                                 foreach ($circuit as $key => $item) {

                //                                     $roleIds[] = $item['role_id'];
                //                                 }

                //                                 $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                                 $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                                 $arrayKeys = array_keys($roleIds);

                //                                 $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                                 if (in_array($record->validation_step, $indicesDesired)) {
                //                                     return true;
                //                                 } else {
                //                                     return false;
                //                                 }
                //                             }

                //                         } else {
                //                             return false;
                //                         }

                //                     }),
                //             ]),

                //     ])->visible(function ($record) {
                //         if ($record) {

                //             if ($record->validation_state == 'nextValue') {
                //                 return true;
                //             } else {

                //                 $circuit = Circuit::find($record->circuit_id)->steps;

                //                 foreach ($circuit as $key => $item) {

                //                     $roleIds[] = $item['role_id'];
                //                 }

                //                 $searchedRoleId = (Role::where('name', RolesEnum::Directeur_general()->value)->first())->id;

                //                 $firstOccurenceOfRole = array_search($searchedRoleId, $roleIds); // first array key where role occurs

                //                 $arrayKeys = array_keys($roleIds);

                //                 $indicesDesired = array_slice($arrayKeys, $firstOccurenceOfRole + 1); //remaiing indices

                //                 if (in_array($record->validation_step, $indicesDesired)) {
                //                     return true;
                //                 } else {
                //                     return false;
                //                 }
                //             }

                //         } else {
                //             return false;
                //         }

                //     }),
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
                    ->label('Statut de validation')
                    ->formatStateUsing(function ($state) {
                        // dd($state);
                        if ($state == 'nextValue') {
                            return 'Terminée';
                        } elseif ($state == ReparationValidationStates::Rejete()->value) {

                            return 'Rejetée';

                        } else {
                            $validator = (Role::find($state))->name;

                            return 'En attente de validation de: '.$validator;
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
                                fn (Builder $query, $date): Builder => $query->whereDate('date_lancement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_lancement', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date d\'envoi en réparation:  '.Carbon::parse($data['date_from'])->format('d-m-Y').' au '.Carbon::parse($data['date_to'])->format('d-m-Y');
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
                        if (! $data['prestataire_id']) {
                            return null;
                        }

                        return 'Prestataire: '.Prestataire::where('code_fr', $data['prestataire_id'])->value('raison_social_fr');
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
