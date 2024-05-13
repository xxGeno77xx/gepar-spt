<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Models\Enginetvm;
use App\Models\Tvm;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

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

                Tables\Columns\TextColumn::make('reference'),

                Tables\Columns\TextColumn::make('date_debut')
                    ->date("d M Y"),

                Tables\Columns\TextColumn::make('date_fin')
                    ->date("d M Y"),

                
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

        
        return Enginetvm::join("tvms", "tvms.id", "engine_tvm.tvm_id")
        ->select("date_debut", "date_fin", "reference", "engine_tvm.id")
        ->where("engine_tvm.engine_id", $this->getOwnerRecord()->id);
    }
}
