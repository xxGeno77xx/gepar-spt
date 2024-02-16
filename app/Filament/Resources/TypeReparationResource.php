<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeReparationResource\Pages;
use App\Models\TypeReparation;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;

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

                Hidden::make('state')
                    ->default(StatesClass::Activated()->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle')
                    ->searchable(query: function (Builder $query, string $search): Builder {

                        return $query->selectRaw('libelle')->whereRaw('LOWER(libelle) LIKE ?', ['%'.strtolower($search).'%']);

                    }),
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
            ])->defaultSort('created_at', 'desc');
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
