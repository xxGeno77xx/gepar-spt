<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Carburant;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Support\Database\StatesClass;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CarburantResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CarburantResource\RelationManagers;

class CarburantResource extends Resource
{
    protected static ?string $model = Carburant::class;

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('type_carburant')
                    ->unique(ignoreRecord:true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type_carburant')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('Supprimer')
                ->color('danger')
                ->action(function (?Carburant $record) {
                    $record->update(['state' => StatesClass::Deactivated()->value]);
                    redirect('/carburants');
                    Notification::make()
                        ->title('Supprimé(e)')
                        ->success()
                        ->persistent()
                        ->send();
                })
                ->requiresConfirmation(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCarburants::route('/'),
        ];
    }    
    

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
        ->where('carburants.state',StatesClass::Activated());
    }
}
