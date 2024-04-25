<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModeleResource\Pages;
use App\Models\Marque;
use App\Models\Modele;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ModeleResource extends Resource
{
    protected static ?string $model = Modele::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $modelLabel = 'Modèles';

    protected static ?string $navigationIcon = 'heroicon-o-color-swatch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nom_modele')
                    ->required()
                    ->unique(ignoreRecord: true),

                Hidden::make('state')
                    ->default(StatesClass::Activated()->value),

                Select::make('marque_id')
                // ->relationship('marque','nom_marque')
                ->searchable()
                    ->label('Marque')
                    ->options(Marque::select('nom_marque', 'id')->where('state', StatesClass::Activated()->value)->pluck('nom_marque', 'id'))
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom_modele')
                    ->label('Modèle')
                    ->sortable(),

                TextColumn::make('nom_marque')
                    ->label('Marque')
                    ->searchable(query: function (Builder $query, string $search): Builder {

                        return $query->selectRaw('modeles.nom_modele')->whereRaw('LOWER(nom_modele) LIKE ?', ['%'.strtolower($search).'%']);

                    }),

                ImageColumn::make('logo')
                    ->label('Logo')
                    ->alignment('center')
                    ->defaultImageUrl(url('images/no_logo.png')),

            ])//->defaultSort('nom_marque', 'desc')

            ->filters([
                //
            ])

            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListModeles::route('/'),
            'create' => Pages\CreateModele::route('/create'),
            'edit' => Pages\EditModele::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::modeles_read()->value,
            PermissionsClass::modeles_update()->value,
        ]);
    }
}
