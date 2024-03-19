<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use Closure;
use Carbon\Carbon;
use Filament\Tables;
use App\Models\Chauffeur;
use App\Models\Departement;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\OrdreDeMission;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class OrdreDeMissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'ordreDeMissions';

    protected static ?string $title = 'Ordres de mission';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ordre')
                    ->label("Numéro d'ordre"),

                BadgeColumn::make('chauffeur_id')
                    ->label('Chauffeur')
                    ->formatStateUsing(fn ($state): string => Chauffeur::find($state)->fullname)
                    ->color('success'),

                BadgeColumn::make('date_de_depart')
                    ->date('d-m-Y')
                    ->color('primary'),

                BadgeColumn::make('date_de_retour')
                    ->date('d-m-Y')
                    ->color('danger'),

                BadgeColumn::make('objet_mission')
                    ->limit(25)
                    ->color('success')
                    ->label('Objet de la mission'),

                BadgeColumn::make('lieu')
                    ->color('success')
                    ->searchable(),

                    BadgeColumn::make('departement_id')
                    ->label('Département')
                    ->formatStateUsing(fn ($state) => Departement::where('code_centre', $state)->first()->sigle_centre)
                    ->color('success'),
            ])
            ->filters([
                Filter::make('Departement')
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
                                    fn (Builder $query, $status): Builder => $query->where('ordre_de_missions.chauffeur_id', $status),
                                );
                        })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['chauffeur_id']) {
                            return null;
                        }

                        return 'Chauffeur: '.Chauffeur::where('id', $data['chauffeur_id'])->first()->fullname;
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
                                fn (Builder $query, $date): Builder => $query->whereDate('date_de_depart', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_de_depart', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date de départ:  '.Carbon::parse($data['date_from'])->format('d-m-Y').' au '.Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),

               

            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('printC')
                        ->label('PDF (couleur)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn (OrdreDeMission $record) => route('couleur', $record)) //this to orders
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('printNB')
                        ->label('PDF (Noir & Blanc)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn (OrdreDeMission $record) => route('pdfNoirBlanc', $record)) //this to orders
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // protected function getTableQuery(): Builder
    // {

    //     $query =
    //     return parent::getTableQuery()
    //         ->join('engines', 'assurances.engine_id', '=', 'engines.id')
    //         ->select('engines.plate_number', 'assurances.*')
    //         ->whereNull('assurances.deleted_at')
    //         ->where('assurances.state', '=', StatesClass::Activated()->value);
    // }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Model $record): string => route('filament.resources.ordre-de-missions.view', ['record' => $record]);
    }
}
