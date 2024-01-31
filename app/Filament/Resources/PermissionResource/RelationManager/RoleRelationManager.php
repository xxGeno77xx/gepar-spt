<?php

namespace App\Filament\Resources\PermissionResource\RelationManager;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Resources\Table;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Columns\TextColumn;
use Spatie\Permission\PermissionRegistrar;

class RoleRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             TextInput::make('name')
    //                 ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
    //             TextInput::make('guard_name')
    //                 ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
    //                  ->default(config('auth.defaults.guard')),

    //         ]);

    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
                // TextColumn::make('guard_name')
                //     ->label(strval(__('filament-authentication::filament-authentication.field.guard_name'))),

            ])
            ->filters([
                //
            ])

            ->actions([
                // AttachAction::make()->preloadRecordSelect()
            ])
            ->bulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public function afterAttach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function afterDetach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getResourceTable(): Table
    {
        $table = Table::make();

        return $this->table($table);
    }
}
