<?php

namespace App\Filament\Resources;

use App\Support\Database\StatesClass;
use Filament\Forms;
use Filament\Tables;
use App\Models\Marque;
use App\Models\Modele;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ModeleResource\Pages;
use Filament\Tables\Columns\Layout\Grid;
class ModeleResource extends Resource
{
    protected static ?string $model = Modele::class;
    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $modelLabel = "Modèles";
    protected static ?string $navigationIcon = 'heroicon-o-color-swatch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nom_modele')
                ->required()
                ->unique(ignoreRecord:true),
                
                Select::make('marque_id')
                // ->relationship('marque','nom_marque')
                ->label("Marque")
                ->options(Marque::select('nom_marque','id')->where('state',StatesClass::Activated())->pluck('nom_marque','id'))
                ->searchable()
                ->required(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom_modele')
                    ->label('Modèle')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nom_marque')
                    ->label('Marque')
                    ->sortable()
                    ->searchable(),

                ImageColumn::make('logo')
                ->label('Logo')
                ->alignment('center')
                ->defaultImageUrl(url('images/no_logo.png')),
                
            ])->defaultSort('created_at','desc')

            ->filters([
                //
            ])

            ->actions([
                // Tables\Actions\ViewAction::make(),
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
