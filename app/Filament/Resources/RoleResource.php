<?php

namespace App\Filament\Resources;

use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ViewRole;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\RelationManager\PermissionRelationManager;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
protected static ?string $modelLabel = "Rôles";
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public function __construct()
    {
        static::$model = config('filament-authentication.models.Role');
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.role'));
    }

    protected static function getNavigationGroup(): ?string
    {
        return strval(__('filament-authentication::filament-authentication.section.group'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.roles'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        
                        Grid::make(2)
                            ->schema([

                                TextInput::make('name')
                                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                                    ->required(),

                                TextInput::make('guard_name')
                                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                                    ->required()
                                    ->default(config('auth.defaults.guard')),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PermissionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
            'view' => ViewRole::route('/{record}'),
        ];
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Roles_create()->value,
            PermissionsClass::Roles_read()->value,

           
        ]);
    }
}
