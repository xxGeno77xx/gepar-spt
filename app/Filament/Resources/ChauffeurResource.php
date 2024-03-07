<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChauffeurResource\Pages;
use App\Filament\Resources\ChauffeurResource\RelationManagers\OrdreDeMissionsRelationManager;
use App\Models\Chauffeur;
use App\Models\Departement;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class ChauffeurResource extends Resource
{
    protected static ?string $model = Chauffeur::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    // protected static bool $shouldRegisterNavigation = false;

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('fullname')
                                    ->label('Nom complet')
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                // Select::make('departement_id')
                                //     ->label('Département')
                                //     ->options(
                                //         Departement::select(['sigle_centre', 'code_centre'])
                                //             ->where('sigle_centre', '<>', '0')
                                //             ->pluck('sigle_centre', 'code_centre')
                                //     )
                                //     ->searchable()
                                //     ->required(),
                            ]),
                    ]),

                Card::make()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Ajouté')
                            ->content(fn (Chauffeur $record): ?string => $record->created_at),

                        Placeholder::make('updated_at')
                            ->label('Mise à jour')
                            ->content(fn (Chauffeur $record): ?string => $record->updated_at),

                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Chauffeur $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullname')
                    ->searchable()
                    ->label('Nom complet')
                    ->placeholder('-'),

                // TextColumn::make('nom_departement')
                //     ->label('Département')
                //     ->placeholder('-'),

                // TextColumn::make('plate_number')
                //     ->label('Engin')
                //     ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('chauffeurs.created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            OrdreDeMissionsRelationManager::class,
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
