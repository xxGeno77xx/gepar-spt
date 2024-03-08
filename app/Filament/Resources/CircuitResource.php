<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CircuitResource\Pages;
use App\Models\Circuit;
use App\Models\Role;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class CircuitResource extends Resource
{
    protected static ?string $model = Circuit::class;

    protected static ?string $navigationGroup = 'REGLAGES';
    protected static ?string $navigationIcon = 'heroicon-o-chevron-double-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                Repeater::make('steps')
                    ->schema([

                        Select::make('role_id')
                            ->searchable()
                            ->label('roles')
                            ->options(Role::pluck('name', 'id')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCircuits::route('/'),
            'create' => Pages\CreateCircuit::route('/create'),
            'edit' => Pages\EditCircuit::route('/{record}/edit'),
        ];
    }
}
