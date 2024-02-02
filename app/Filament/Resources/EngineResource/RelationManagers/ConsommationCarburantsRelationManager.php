<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Chauffeur;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Grid;
use App\Models\ConsommationCarburant;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

class ConsommationCarburantsRelationManager extends RelationManager
{
    protected static string $relationship = 'consommationCarburants';

    protected static ?string $title = 'Carburant & kilometrage';

    protected static ?string $recordTitleAttribute = '';

    public static function form(Form $form): Form
    {
        // dd(function (RelationManager $livewire): array {
        //     return $livewire->ownerRecord->stores()
        //         ->value('id');
        // });

        return $form
            ->schema([

                Grid::make(3)
                    ->schema([
                        Forms\Components\DatePicker::make('date') // to do: make it unique for every engine
                            ->required(),

                        Forms\Components\TextInput::make('ticket')
                            ->unique(ignoreRecord:true)
                            ->required(),

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
                            ]),


                        Forms\Components\TextInput::make('kilometres_a_remplissage')
                            ->label('Kilometrage au remplissage')
                            ->numeric()
                            ->suffix('Km')
                            ->minValue(0)
                            ->required()
                            ->rules([
                                function (RelationManager $livewire) {

                                    return function (string $attribute, $value, Closure $fail) use ($livewire) {

                                        $latestConsommation = ConsommationCarburant::latest()
                                            ->where('engine_id', $livewire->ownerRecord->id)
                                            ->value('kilometres_a_remplissage');

                                            // dd( $latestConsommation);
                                        if ($latestConsommation) {

                                            if ($value < $latestConsommation) {
                                                // $fail('Le champ :attribute doit être supérieur à 0.');
                                                $fail('Le dernier kilométrage était à ' . $latestConsommation . ' km');
                                            }
                                        }

                                    };
                                },
                            ]),

                        Forms\Components\Select::make('chauffeur_id')
                            ->label('Chauffeur')
                            ->options(
                                Chauffeur::select(['name', 'id'])->get()->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable(),



                        Forms\Components\TextInput::make('carte_recharge_id')
                            ->label('Carte de recharge')
                            ->required(),


                    ]),
                Forms\Components\TextInput::make('observation')
                    ->required()
                    ->columnSpanFull(),

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
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('ticket')
                    ->label('Numéro du ticket'),

                Tables\Columns\TextColumn::make('quantite')
                    ->label('Quantité en litres'),

                Tables\Columns\TextColumn::make('kilometres_a_remplissage')
                    ->label('Kilometrage au remplissage'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Chauffeur'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Ajouter'),
                Tables\Actions\Action::make('export')
                    ->label('Récapitulatif')

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('consommation_carburants.date', 'asc');
    }

    public function getTableQuery():Builder
    {
        return ConsommationCarburant::leftJoin('chauffeurs', 'consommation_carburants.chauffeur_id','chauffeurs.id')
        ->select(['consommation_carburants.*', 'chauffeurs.name']);
    }
}
