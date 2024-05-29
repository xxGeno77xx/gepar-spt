<?php

namespace App\Filament\Resources\ChauffeurResource\RelationManagers;

use App\Models\AffectationChauffeur;
use App\Models\Engine;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AffectationChauffeursRelationManager extends RelationManager
{
    protected static string $relationship = 'affectationChauffeurs';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('old_engine_id')
                    ->label('ancien engin')
                    ->formatStateUsing(function ($state) {

                        $string = '-';
                        try {
                            $string = Engine::where('id', $state)->first()->plate_number;
                        } catch (\Exception $e) {

                        }

                        return $string;
                    }),

                Tables\Columns\TextColumn::make('new_engine_id')
                    ->label('nouvel engin')
                    ->formatStateUsing(fn ($state) => Engine::where('id', $state)->first()->plate_number),

                Tables\Columns\TextColumn::make('date_affectation')
                    ->date('d M Y'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function getTableQuery(): Builder
    {

        return AffectationChauffeur::join('chauffeurs', 'chauffeurs.id', '=', 'affectation_chauffeurs.chauffeur_id')
            ->where('chauffeurs.id', $this->ownerRecord->id)
            ->select('chauffeurs.id', 'old_engine_id', 'new_engine_id', 'date_affectation')
            ->orderBy("date_affectation", "desc");
    }
}
