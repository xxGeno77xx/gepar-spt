<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarqueResource\Pages;
use App\Models\Marque;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;

class MarqueResource extends Resource
{
    protected static ?string $model = Marque::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $navigationIcon = 'heroicon-o-scissors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nom_marque')
                    ->label('Nom de la marque')
                    ->required()
                    ->unique(ignoreRecord: true),

                Hidden::make('state')
                    ->default(StatesClass::Activated()->value),

                FileUpload::make('logo')
                    ->disk('public')
                    ->enableDownload()
                    ->enableOpen()
                    ->imageResizeTargetWidth('1300')
                    ->imageResizeTargetHeight('1200'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->label('ID'),

                TextColumn::make('nom_marque')
                    ->searchable(query: function (Builder $query, string $search): Builder {

                        return $query->selectRaw('nom_marque')->whereRaw('LOWER(nom_marque) LIKE ?', ['%'.strtolower($search).'%']);

                    }),

                ImageColumn::make('logo')
                    ->alignment('center'),
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
            'index' => Pages\ListMarques::route('/'),
            'create' => Pages\CreateMarque::route('/create'),
            'edit' => Pages\EditMarque::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::marques_read()->value,
            PermissionsClass::marques_update()->value,
        ]);
    }
}
