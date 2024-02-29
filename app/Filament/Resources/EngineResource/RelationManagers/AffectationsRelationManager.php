<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Models\Departement;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class AffectationsRelationManager extends RelationManager
{
    protected $listeners = ['refreshAffectations' => 'refresh'];

    protected static string $relationship = 'affectations';

    protected static ?string $recordTitleAttribute = 'id';

    public function refresh()
    {

    }

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

                Tables\Columns\BadgeColumn::make('date_reaffectation')
                    ->color('success')
                    ->date('d-m-Y')
                    ->label('Affecté le'),

                Tables\Columns\TextColumn::make('departement_origine_id')
                    ->label('De')
                    ->formatStateUsing(fn ($state): string => Departement::find($state)->sigle_centre),

                Tables\Columns\TextColumn::make('departement_cible_id')
                    ->label('Vers')
                    ->formatStateUsing(fn ($state): string => Departement::find($state)->sigle_centre),

            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
