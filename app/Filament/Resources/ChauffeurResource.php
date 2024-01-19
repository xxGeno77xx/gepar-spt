<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Chauffeur;
use App\Models\Departement;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ChauffeurResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ChauffeurResource\RelationManagers;

class ChauffeurResource extends Resource
{
    protected static ?string $model = Chauffeur::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),

                Select::make('departement_id')
                    ->label('Département')
                    ->options(
                        Departement::where('state', StatesClass::Activated())
                            // ->whereNull('chauffeur_id')
                            ->pluck('nom_departement', 'id')
                    )
                    ->searchable()
                    ->required(),

                Card::make()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Ajouté')
                            ->content(fn(Chauffeur $record): ?string => $record->created_at),

                        Placeholder::make('updated_at')
                            ->label('Mise à jour')
                            ->content(fn(Chauffeur $record): ?string => $record->updated_at),

                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Chauffeur $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('nom_departement')
                    ->label('Département')
                    ->placeholder('-'),

                TextColumn::make('plate_number')
                    ->label('Engin')
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('chauffeurs.created_at', 'desc');
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
            'index' => Pages\ListChauffeurs::route('/'),
            'create' => Pages\CreateChauffeur::route('/create'),
            'edit' => Pages\EditChauffeur::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::chauffeurs_read()->value,
            PermissionsClass::chauffeurs_update()->value,
            PermissionsClass::chauffeurs_create()->value,
        ]);
    }
}
