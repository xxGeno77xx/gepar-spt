<?php

namespace App\Filament\Resources\ChauffeurResource\RelationManagers;

use App\Models\Departement;
use App\Models\Engine;
use App\Models\OrdreDeMission;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrdreDeMissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'ordreDeMissions';

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

                BadgeColumn::make('engine_id')
                    ->label('Moyen de transport')
                    ->formatStateUsing(fn ($state): string => Engine::find($state)->plate_number)
                    ->color('success'),

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
                                fn (Builder $query, $date): Builder => $query->where('engine_id', '=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['engine_id']) {
                            return 'Moyen de transport:  '.Engine::where('id', ($data['engine_id']))->first()->plate_number;
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

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Model $record): string => route('filament.resources.ordre-de-missions.view', ['record' => $record]);
    }
}
