<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarburantResource\Pages;
use App\Models\Carburant;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

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
                    ->unique(ignoreRecord: true),

                Hidden::make('state')->default(StatesClass::Activated()->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type_carburant'),
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
            ->where('carburants.state', StatesClass::Activated()->value);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Carburant_create()->value,
            PermissionsClass::Carburant_read()->value,
            PermissionsClass::Carburant_update()->value,
        ]);
    }
}
