<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeResource\Pages;
use App\Models\Type;
use App\Support\Database\PermissionsClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $navigationIcon = 'heroicon-o-view-grid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nom_type')
                    ->label('Nom du type')
                    ->required()
                    ->unique(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nom_type')
                    ->label('Nom')
                    ->searchable(),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        // return auth()->user()->hasAnyPermission([
        //     PermissionsClass::departements_read()->value,
        //     PermissionsClass::departements_update()->value,
        // ]);

        return auth()->user()->hasRole([
            RolesPermissionsSeeder::SuperAdmin,
        ]);
    }
}
