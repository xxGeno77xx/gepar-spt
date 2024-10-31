<?php

namespace App\Filament\Resources;

use App\Models\Role;
use Filament\Tables;
use App\Models\Circuit;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Database\Seeders\RolesPermissionsSeeder;
use App\Filament\Resources\CircuitResource\Pages;

class CircuitResource extends Resource
{
    protected static ?string $model = Circuit::class;

    protected static ?string $navigationGroup = 'REGLAGES';

    protected static ?string $navigationIcon = 'heroicon-o-chevron-double-right';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(RolesEnum::Super_administrateur()->value);
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
