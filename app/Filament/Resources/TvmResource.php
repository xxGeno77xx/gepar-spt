<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TvmResource\Pages;
use App\Models\Engine;
use App\Models\Tvm;
use App\Support\Database\RolesEnum;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;

class TvmResource extends Resource
{
    protected static ?string $model = Tvm::class;

    protected static ?string $navigationGroup = 'Documents administratifs';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Card::make()
                    ->columns(2)
                    ->schema([

                        DatePicker::make('date_debut')
                            ->before('date_fin')
                            ->label('Date initiale')
                            ->reactive()
                            ->afterStateUpdated(fn ($set, $get) => $set('date_fin', ((Carbon::parse($get('date_debut')))->endOfYear())))
                            ->required(),

                        DatePicker::make('date_fin')
                            ->label("Date d'expiration")
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('engine_id')
                            ->label('Engins')
                            ->options(Engine::whereNot('state', StatesClass::Deactivated()->value)->pluck('plate_number', 'id'))
                            ->preload()
                            ->searchable()
                            ->required()
                            ->visibleOn('edit'),

                        TextInput::make('prix')
                            ->numeric()
                            ->required()
                            ->visibleOn('edit'),

                        TextInput::make('reference')
                            ->label('N° lot')
                            ->required()
                            ->numeric()
                            ->required()
                            ->visibleOn('edit')
                            ->columnSpanFull(),

                        Repeater::make('engins_prix')
                            ->label('Informations')
                            ->minItems(1)
                            ->disabledOn('edit')
                            ->visibleOn('create')
                            ->createItemButtonLabel('Ajouter un engin')
                            ->columnSpanFull()

                            ->schema([
                                Grid::make(3)
                                    ->schema([

                                        Select::make('engine_id')
                                            ->label('Engins')
                                            ->options(Engine::whereNot('state', StatesClass::Deactivated()->value)->pluck('plate_number', 'id'))
                                            ->preload()
                                            ->searchable()
                                            ->required()
                                            ->reactive(),

                                        TextInput::make('prix')
                                            ->required()
                                            ->numeric(),

                                        TextInput::make('reference')
                                            ->label('N° lot')
                                            ->required()
                                            ->numeric()
                                            ->unique(ignoreRecord: true)
                                            ->required(),
                                    ]),

                            ]),
                        Hidden::make('user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),

                        Hidden::make('updated_at_user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('N° lot'),

                BadgeColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->color('success'),

                TextColumn::make('date_debut')
                    ->date('d M Y'),

                TextColumn::make('date_fin')
                    ->date('d M Y'),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),*
                Tables\Actions\BulkAction::make('editer')
                    ->form([
                        Card::make()
                            ->columns(3)
                            ->schema([

                                DatePicker::make('date_debut')
                                    ->before('date_fin')
                                    ->label('Date initiale')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($set, $get) => $set('date_fin', ((Carbon::parse($get('date_debut')))->endOfYear())))
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label("Date d'expiration")
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ]),

                    ])
                    ->action(function (Collection $records, $data) {

                        $records->each->update([
                            'date_debut' => $data['date_debut'],
                            'date_fin' => $data['date_fin'],
                        ]);

                        Notification::make('notif')
                            ->title('Modifié(e)')
                            ->icon('heroicon-o-information-circle')
                            ->iconColor('success')
                            ->body('Les TVM ont été modifiées')
                            ->send();
                    }),

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
            'index' => Pages\ListTvms::route('/'),
            'create' => Pages\CreateTvm::route('/create'),
            'edit' => Pages\EditTvm::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole([

            RolesEnum::Chef_parc()->value,
            RolesEnum::Dpl()->value,
            RolesPermissionsSeeder::SuperAdmin,

        ]);
    }
}
