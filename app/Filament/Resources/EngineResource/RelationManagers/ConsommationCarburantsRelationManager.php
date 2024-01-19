<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Carburant;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Grid;
use App\Models\ConsommationCarburant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                        Forms\Components\TextInput::make('quantite')
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
                            ->label('Kilometrage au moment du remplissage')
                            ->numeric()
                            ->suffix('Km')
                            ->minValue(0)
                            ->required()
                            ->rules([
                                function (RelationManager $livewire) {

                                    return function (string $attribute, $value, Closure $fail  ) use ($livewire) {

                                        $latestConsommation = ConsommationCarburant::latest()
                                        ->where('engine_id', $livewire->ownerRecord->id)
                                        ->value('kilometres_a_remplissage');

                                        if ($latestConsommation) {

                                            if ($value < $latestConsommation) {
                                                // $fail('Le champ :attribute doit être supérieur à 0.');
                                                $fail('Le dernier kilométrage était à ' . $latestConsommation . ' km');
                                            }
                                        }

                                    };
                                },
                            ]),


                        Forms\Components\DatePicker::make('date')
                            ->unique()
                            ->required(),
                    ]),


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
                Tables\Columns\TextColumn::make('quantite')
                    ->label('Quantité en litres'),
                Tables\Columns\TextColumn::make('kilometres_a_remplissage')
                    ->label('Kilométrage au moment du remplissage'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d-m-Y')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Ajouter'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
