<?php

namespace App\Filament\Resources;


use Database\Seeders\RolesPermissionsSeeder;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use Phpsa\FilamentAuthentication\Actions\ImpersonateLink;

class UserResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $recordTitleAttribute = 'name';

    public function __construct()
    {
        static::$model = config('filament-authentication.models.User');
    }

    protected static function getNavigationGroup(): ?string
    {
        return strval(__('filament-authentication::filament-authentication.section.group'));
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.user'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.users'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.name')))
                            ->required(),

                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->unique(table: static::$model, ignorable: fn($record) => $record)
                            ->regex('/.*@laposte\.tg$/') // field must end with @laposte.tg
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),

                        TextInput::make('password')
                            ->same('passwordConfirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn($component, $get, $livewire, $model, $record, $set, $state) => $record === null)
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : '')
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.password'))),

                        TextInput::make('passwordConfirmation')
                            ->password()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.confirm_password'))),

                        //removed super admin from roles list here
                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            // line below removes super admin role from roles list when attributing roles to newly created users
                            // ->relationship('roles', 'name',fn (Builder $query) => $query->whereNot('name',RolesPermissionsSeeder::SuperAdmin))
                            ->preload(true)
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),


                        // Radio::make('state')
                        // ->label('Etat')
                        //     ->options([
                        //         StatesClass::Activated()->value  => 'Activé' ,
                        //         StatesClass::Deactivated()->value => 'Désactivé',
                        //         StatesClass::Suspended()->value => 'Suspendu'

                        //     ])
                    ])->columns(2),
                Toggle::make('notification')
                    ->offIcon('heroicon-o-mail')
                    ->onIcon('heroicon-o-mail-open')
                    ->inline()
                    ->label('Notifications'),

                // Toggle::make('state')
                // ->label('Etat')
                // ->default(true),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.name'))),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),

                IconColumn::make('notification')
                    ->trueIcon('heroicon-o-badge-check')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Notifications')
                    ->alignment('center'),

                TagsColumn::make('roles.name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),
                TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i:s')
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.created_at'))),
            ])
            ->filters([

                TernaryFilter::make('email_verified_at')
                    ->label(strval(__('filament-authentication::filament-authentication.filter.verified')))
                    ->nullable(),
                    
                TernaryFilter::make('state')
                    ->label(strval(__('Etat')))
                    ->trueLabel('Activé')
                    ->falseLabel('Désactivé')
                    ->nullable()
                    ->queries(
                        true: fn(Builder $query) => $query->where('state', StatesClass::Activated()),
                        false: fn(Builder $query) => $query->where('state', StatesClass::Deactivated()),
                    ),
            ])
            ->prependActions([
                ImpersonateLink::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
        ;
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([

            PermissionsClass::users_create()->value,
            PermissionsClass::users_read()->value,
            PermissionsClass::users_update()->value,


        ]);
    }


}
