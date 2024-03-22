<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdreDeMissionResource\Pages;
use App\Models\Chauffeur;
use App\Models\Departement;
use App\Models\Engine;
use App\Models\OrdreDeMission;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class OrdreDeMissionResource extends Resource
{
    protected static ?string $model = OrdreDeMission::class;

    protected static ?string $navigationGroup = 'Missions';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Card::make()
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                Select::make('chauffeur_id')
                                    ->label('Chauffeur')
                                    ->options(
                                        Chauffeur::select(['fullname', 'id'])
                                            ->where('Chauffeurs.state', StatesClass::Activated()->value)
                                            ->get()
                                            ->pluck('fullname', 'id')
                                    )
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $get, $set) {

                                        $chauffeur = Chauffeur::where("id", $state)->first();

                                        $linkedEngine = Engine::where("id", $chauffeur->engine_id)->first();

                                        $linkedDepartement = Departement::where("code_centre", $linkedEngine->departement_id)->first();

                                        $set("engine_id", $linkedEngine->id);

                                        $set("departement_id", $linkedDepartement->code_centre);

                                    })
                                    ->searchable(),

                                DatePicker::make('date_de_depart')
                                    ->required(),

                                DatePicker::make('date_de_retour')
                                    ->afterOrEqual('date_de_depart')
                                    ->required(),

                                Repeater::make('agents')
                                    ->schema([

                                        Grid::make(2)

                                            ->schema([
                                                TextInput::make('Nom')
                                                    ->label('Nom complet')
                                                    ->required(),

                                                TextInput::make('Désignation')
                                                    ->required(),

                                            ]),
                                    ])
                                    ->createItemButtonLabel('Ajouter un agent')
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('engine_id')
                                            ->label('Moyen de transport')
                                            ->options(
                                                Engine::select(['plate_number', 'id'])
                                                    ->where('engines.state', StatesClass::Activated()->value)
                                                    ->get()
                                                    ->pluck('plate_number', 'id')
                                            )
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $get, $set) {

                                                $linkedEngine = Engine::where("id", $state)->first();

                                                $linkedDepartement = Departement::where("code_centre", $linkedEngine->departement_id)->first();

                                                $set("departement_id", $linkedDepartement->code_centre);

                                            }),

                                        Select::make('departement_id')
                                            ->label('Département')
                                            ->options(
                                                Departement::select(['sigle_centre', 'code_centre'])
                                                    ->get()
                                                    ->pluck('sigle_centre', 'code_centre')
                                            )
                                            ->searchable()
                                            ->required(),

                                        TagsInput::make('lieu')
                                            ->label('Destination(s)')
                                            ->required()
                                            ->placeholder('Nouvelle destination'),

                                        TextInput::make('objet_mission')
                                            ->label('Objet de la mission')
                                            ->required(),
                                    ]),

                                Hidden::make('numero_ordre')
                                    ->default(fn() => OrdreDeMission::orderBy('id', 'desc')->first() ? OrdreDeMission::orderBy('id', 'desc')->first()->id + 1 : 1), //generate the number
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ordre')
                    ->label("Numéro d'ordre")
                    ->searchable(isIndividual: true),

                TextColumn::make('chauffeur')
                    ->label('Chauffeur'),

                BadgeColumn::make('date_de_depart')
                    ->date('d-m-Y')
                    ->color('primary'),

                BadgeColumn::make('date_de_retour')
                    ->date('d-m-Y')
                    ->color('danger'),

                BadgeColumn::make('objet_mission')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->objet_mission)
                    ->color('success')
                    ->label('Objet de la mission'),

                TagsColumn::make('lieu')
                    ->label('Destination(s)')
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search): Builder {

                        return $query->selectRaw('ordre_de_missions.lieu')->whereRaw('LOWER(lieu) LIKE ?', ['%' . strtolower($search) . '%']);

                    }),

                BadgeColumn::make('plate_number')
                    ->label('Moyen de transport')
                    ->color('success'),

                BadgeColumn::make('departement_id')
                    ->label('Département')
                    ->formatStateUsing(fn($state) => Departement::where('code_centre', $state)->first()->sigle_centre)
                    ->color('success'),
            ])
            ->filters([
                Filter::make('Departement')
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['departement_id']) {
                            return null;
                        }

                        return 'Département: ' . Departement::where('code_centre', $data['departement_id'])->value('sigle_centre');
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
                                    $search = Departement::where('code_centre', $status)->value('code_centre');

                                    return $query->where('ordre_de_missions.departement_id', $search);
                                }
                            );
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
                                fn(Builder $query, $status): Builder => $query->where('ordre_de_missions.chauffeur_id', $status),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['chauffeur_id']) {
                            return null;
                        }

                        return 'Chauffeur: ' . Chauffeur::where('id', $data['chauffeur_id'])->first()->fullname;
                    }),

                Filter::make('date_de_depart')
                    ->label('Date de départ')
                    ->form([

                        Fieldset::make('Date de départ')
                            ->schema([

                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('date_from')
                                            ->label('Du')
                                            ->columnSpanFull(),
                                        DatePicker::make('date_to')
                                            ->label('Au')
                                            ->columnSpanFull(),

                                    ]),
                            ]),

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_de_depart', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_de_depart', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date de départ:  ' . Carbon::parse($data['date_from'])->format('d-m-Y') . ' au ' . Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),

                Filter::make('engine_id')
                    ->label('Moyen de transport')
                    ->form([
                        Grid::make(2)
                            ->schema([

                                Select::make('engine_id')
                                    ->label('Moyen de transport')
                                    ->options(Engine::pluck('plate_number', 'id'))
                                    ->searchable(),

                            ])->columns(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['engine_id'],
                                fn(Builder $query, $date): Builder => $query->where('engine_id', '=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['engine_id']) {
                            return 'Moyen de transport:  ' . Engine::where('id', ($data['engine_id']))->first()->plate_number;
                        }

                        return null;
                    }),

            ])
            ->actions([

                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('printC')
                        ->label('PDF (couleur)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn(OrdreDeMission $record) => route('couleur', $record)) //this to orders
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('printNB')
                        ->label('PDF (Noir & Blanc)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn(OrdreDeMission $record) => route('pdfNoirBlanc', $record)) //this to orders
                        ->openUrlInNewTab(),
                ]),

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
            'index' => Pages\ListOrdreDeMissions::route('/'),
            'create' => Pages\CreateOrdreDeMission::route('/create'),
            'view' => Pages\ViewOrdreDeMission::route('/{record}'),
            'edit' => Pages\EditOrdreDeMission::route('/{record}/edit'),

        ];
    }
}
