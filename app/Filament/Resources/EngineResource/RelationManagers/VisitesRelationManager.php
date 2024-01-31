<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Support\Database\StatesClass;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class VisitesRelationManager extends RelationManager
{
    protected static string $relationship = 'visites';

    protected static ?string $recordTitleAttribute = 'visite_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('visite_id')
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id'),
                BadgeColumn::make('date_initiale')
                    ->dateTime('d-m-Y')
                    ->color('success')
                    ->alignment('center'),
                BadgeColumn::make('date_expiration')
                    ->dateTime('d-m-Y')
                    ->color('success')
                    ->alignment('center'),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->leftjoin('users', 'visites.user_id', '=', 'users.id')
            ->join('engines', 'visites.engine_id', 'engines.id')
            ->select('engines.plate_number', 'visites.*', 'users.name')
            ->whereNull('engines.deleted_at')
            ->whereNull('visites.deleted_at')
            ->where('engines.state', StatesClass::Activated()->value)
            ->where('visites.state', StatesClass::Activated()->value);

    }
}
