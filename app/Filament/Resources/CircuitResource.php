<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CircuitResource\Pages;
use App\Models\Circuit;
use App\Models\Role;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\Card;
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

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(RolesPermissionsSeeder::SuperAdmin);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du circuit'),
                    ]),

                Card::make()
                    ->schema([

                        Repeater::make('steps')
                            ->label('Etapes')
                            ->schema([

                                Select::make('role_id')
                                    ->searchable()
                                    ->label('roles')
                                    ->options(Role::pluck('name', 'id')),
                            ])->grid(2),

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
