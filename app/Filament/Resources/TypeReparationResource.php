<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\TypeReparation;
use Filament\Resources\Resource;
use App\Support\Database\StatesClass;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TypeReparationResource\Pages;
use App\Filament\Resources\TypeReparationResource\RelationManagers;

class TypeReparationResource extends Resource
{
    protected static ?string $model = TypeReparation::class;

    protected static ?string $label = 'Types de réparations';

    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('libelle'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('Supprimer')
                    ->action(function (?TypeReparation $record) {
                        $record->update(['state' => StatesClass::Deactivated()->value]);
                        redirect('/type-reparations');
                        Notification::make()
                            ->title('Supprimé(e)')
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTypeReparations::route('/'),
        ];
    }
}
