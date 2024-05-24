<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Models\Tvm;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TvmsRelationManager extends RelationManager
{
    protected static string $relationship = 'tvms';

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

                Tables\Columns\TextColumn::make('reference')
                    ->label('N° lot'),

                Tables\Columns\BadgeColumn::make('date_debut')
                    ->date('d M Y')
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('date_fin')
                    ->date('d M Y')
                    ->color('success'),

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

        return Tvm::join('engines', 'engines.id', 'tvms.engine_id')
            ->select('date_debut', 'date_fin', 'reference', 'tvms.id')
            ->where('tvms.engine_id', $this->getOwnerRecord()->id);
    }
}
