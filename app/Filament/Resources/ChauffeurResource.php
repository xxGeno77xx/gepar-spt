<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChauffeurResource\Pages;
use App\Filament\Resources\ChauffeurResource\RelationManagers\AffectationChauffeursRelationManager;
use App\Filament\Resources\ChauffeurResource\RelationManagers\OrdreDeMissionsRelationManager;
use App\Models\Chauffeur;
use App\Models\Engine;
use App\Support\Database\ChauffeursStatesClass;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

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
                Card::make()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('fullname')
                                    ->label('Nom complet')
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                Select::make('engine_id')
                                    ->label('Engin')
                                    ->options(

                                        function ($record) {

                                            //check engines that are already linked to chauffeurs

                                            if (! $record) {

                                                $linkedChauffeurs = Chauffeur::whereNotNull('engine_id')->get();
                                                if (! empty($linkedChauffeurs)) {
                                                    $linkedEnginesIds = [];
                                                    foreach ($linkedChauffeurs as $chauffeur) {

                                                        $linkedEnginesIds[] = $chauffeur->engine_id;
                                                    }

                                                    if ($record) {

                                                        return Engine::where(function (Builder $query) use ($record, $linkedEnginesIds) {
                                                            return $query->whereNotIn('id', $linkedEnginesIds)
                                                                ->where('state', StatesClass::Activated()->value)
                                                                ->orWhere('id', $record->engine_id);

                                                        })->get()->pluck('plate_number', 'id');

                                                    } else {

                                                        return Engine::whereNotIn('id', $linkedEnginesIds)
                                                            ->where('state', StatesClass::Activated()->value)
                                                            ->pluck('plate_number', 'id');
                                                    }
                                                }

                                            } else {
                                                return Engine::where('state', StatesClass::Activated()->value)
                                                    ->pluck('plate_number', 'id');
                                            }

                                        }
                                    )
                                    ->searchable(),

                                Select::make('categories_permis')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('categoriePermis', 'libelle'),
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

                Hidden::make('mission_state')->default(ChauffeursStatesClass::Disponible()->value),
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

                BadgeColumn::make('sigle_centre')
                    ->label('Centre')
                    ->color('primary')
                    ->placeholder('-'),

                BadgeColumn::make('plate_number')
                    ->label('Engin')
                    ->color('success')
                    ->placeholder('-'),

                BadgeColumn::make('mission_state')
                    ->label('Statut')
                    ->colors([

                        'primary' => static fn ($state): bool => $state === ChauffeursStatesClass::En_mission()->value,

                        'success' => static fn ($state): bool => $state === ChauffeursStatesClass::Disponible()->value,
                    ])
                    ->placeholder('-'),

                BadgeColumn::make('categoriePermis.libelle')
                    ->label('Permis')
                    ->color('success')
                    ->placeholder('-'),
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
            AffectationChauffeursRelationManager::class,
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
