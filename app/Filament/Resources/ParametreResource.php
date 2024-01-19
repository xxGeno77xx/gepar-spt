<?php

namespace App\Filament\Resources;

use App\Models\Parametre;
use Filament\Resources\Form;
use Filament\Resources\Table;

use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\ParametreResource\Pages;

class ParametreResource extends Resource
{ 
  
    protected static ?string $navigationGroup = 'REGLAGES';
    protected static ?string $model = Parametre::class;
    protected static ?string $modelLabel = "Paramètres";
    protected static ?string $navigationIcon = 'heroicon-o-cog';
   
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                radio::make('limite')
                    ->options([
                        
                        Parametre::UN_MOIS => Parametre::UN_MOIS_VALUE,

                        Parametre::DEUX_SEMAINES =>Parametre::DEUX_SEMAINES_VALUE,
                        
                        Parametre::UNE_SEMAINE  =>Parametre::UNE_SEMAINE_VALUE ,
                    ]),

                // FileUpload::make('icon'), change images 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('options')
                    ->weight('bold'),
                
                TextColumn::make('nom')
                    ->weight('bold'),

                // ImageColumn::make('icon')
                //     ->alignment('right')
                //     // ->size(90),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
            ])
            ->bulkActions([
                // ,
            ])->contentGrid([
                'md' => 2,
                'xl' => 2,
            ]);
    }
    public static function canCreate(): bool
    {
       return false;
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
            'index' => Pages\ListParametres::route('/'),
            'create' => Pages\CreateParametre::route('/create'),
            'edit' => Pages\EditParametre::route('/{record}/edit'),
        ];
    }    


    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Parametre_update()->value,
        ]);
    }

   
}
