<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartementResource\Pages;
use App\Models\Departement;
use App\Support\Database\PermissionsClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class DepartementResource extends Resource
{
    protected static ?string $model = Departement::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $modelLabel = 'Départements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput::make('nom_departement')
                // ->label("Nom du département")
                // ->required()
                // ->unique(ignoreRecord:true,)

                // Hidden::make('user_id')->label('Ajoutée par')
                // ->default(auth()->user()->name)
                // ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // TextColumn::make('nom_departement')->label('Nom'),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            // 'index' => Pages\ListDepartements::route('/'),
            // 'create' => Pages\CreateDepartement::route('/create'),
            // 'edit' => Pages\EditDepartement::route('/{record}/edit'),
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
