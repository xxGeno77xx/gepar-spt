<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeReparationResource\Pages;
use App\Models\TypeReparation;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

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
                TextInput::make('libelle')
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle'),
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

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::TypesCarburant_create()->value,
            PermissionsClass::TypesCarburant_read()->value,
            PermissionsClass::TypesCarburant_update()->value,
        ]);
    }
}
