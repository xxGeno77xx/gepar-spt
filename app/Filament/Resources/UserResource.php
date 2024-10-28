<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\Departement;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
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
                            ->label('Prénom')
                            ->required(),

                        TextInput::make('lastname')
                            ->label('Nom de famille'),

                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->unique(table: static::$model, ignorable: fn ($record) => $record)
                            ->regex('/.*@laposte\.tg$/') // field must end with @laposte.tg
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),

                        TextInput::make('password')
                            ->same('passwordConfirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn ($component, $get, $livewire, $model, $record, $set, $state) => $record === null)
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : '')
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.password'))),

                        TextInput::make('passwordConfirmation')
                            ->password()
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.confirm_password'))),

                        //removed super admin from roles list here
                        Select::make('roles')
                            ->required()
                            ->multiple()
                            ->relationship('roles', 'name')
                            // line below removes super admin role from roles list when attributing roles to newly created users
                            // ->relationship('roles', 'name',fn (Builder $query) => $query->whereNot('name',RolesPermissionsSeeder::SuperAdmin))
                            ->preload(true)
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),

                        TextInput::make('username')
                            ->label("Nom d'utilisateur")
                            ->required(),

                        Select::make('departement')
                            ->label('Centre')
                            ->multiple()
                            ->relationship('departements', 'sigle_centre')
                            ->searchable()
                            ->required()
                            ->preload(),

                        Hidden::make('state')->default(StatesClass::Activated()->value),

                    ])->columns(2),
                Toggle::make('notification')
                    ->offIcon('heroicon-o-mail')
                    ->onIcon('heroicon-o-mail-open')
                    ->inline()
                    ->label('Notifications'),

                Select::make('departement_id')
                    ->label('Appartenance')
                    ->required()
                    ->options(Departement::pluck('sigle_centre', 'code_centre'))
                    ->searchable()
                    ->preload(),

                Card::make()
                    ->schema([
                        Placeholder::make('Aide')
                            ->content(new HtmlString('
                            <p> Pour les directeurs, le centre regroupe la direction et les divisions affiliées. Il est utilisé pour  définir  les correspondances  dans les circuits de validation.
                                </br></br>
                                Exemple:  pour le courrier et réseau (DCR)  Celui qui a le role de Directeur, doit  appartenir aux centres DCR, EMS, DAT et tout ce qui va avec
                                </br></br>
                                L\'appartenance est utilisée pour définir la relation pour filtrer les engins suivant le département de  l\'utilisateur connecté.
                            </p>
                            
                            <br>

                            Note 2: certains Rôles sont incompatibles. Ex chef parc et délégué de division
    ')),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->searchable()
                    ->label('Prénom')
                    ->sortable(),

                TextColumn::make('lastname')
                    ->label('Nom de famille')
                    ->searchable(),

                TextColumn::make('poste')
                    ->label('Poste occupé')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('email')
                //     ->searchable()
                //     ->sortable()
                //     ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),

                // SelectColumn::make('state')
                //     ->label('Etat')
                //     ->disablePlaceholderSelection()
                //     ->options([
                //         StatesClass::Activated()->value,
                //         StatesClass::Suspended()->value,
                //         StatesClass::Deactivated()->value,
                //     ]),

                // IconColumn::make('notification')
                //     ->trueIcon('heroicon-o-badge-check')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->label('Notifications')
                //     ->alignment('center'),

                TagsColumn::make('roles.name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),
                TextColumn::make('created_at')
                    ->sortable()
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
                        true: fn (Builder $query) => $query->where('state', StatesClass::Activated()->value),
                        false: fn (Builder $query) => $query->where('state', StatesClass::Deactivated()->value),
                    ),
            ])
            ->prependActions([
                ImpersonateLink::make(),
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
