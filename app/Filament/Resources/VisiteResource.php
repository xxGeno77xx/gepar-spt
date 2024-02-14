<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisiteResource\Pages;
use App\Models\Engine;
use App\Models\Visite;
use App\Support\Database\CommonInfos;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class VisiteResource extends Resource
{
    protected static ?string $model = Visite::class;

    protected static ?string $navigationGroup = 'Documents administratifs';

    protected static ?string $label = 'Visites techniques';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('engine_id')
                            ->label('Numéro de plaque')
                            ->options(
                                Engine::select(['plate_number', 'id'])
                                    ->where('engines.state', StatesClass::Activated()->value)
                                    ->get()
                                    ->pluck('plate_number', 'id')
                            )
                            ->searchable()
                            ->required(),

                        DatePicker::make('date_initiale')
                            ->before('date_expiration')
                            ->label('Date initiale')
                            ->required(),

                        DatePicker::make('date_expiration')
                            ->label("Date d'expiration")
                            ->required(),

                        Hidden::make('user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),

                        Hidden::make('updated_at_user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),
                    ])
                    ->columnSpan(['lg' => fn (?Visite $record) => $record === null ? 3 : 2]),

                Hidden::make('state')->default(StatesClass::Activated()->value),

                CommonInfos::PlaceholderCard(),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_initiale')
                    ->dateTime('d-m-Y')
                    ->searchable(),

                TextColumn::make('date_expiration')
                    ->dateTime('d-m-Y')
                    ->searchable(),

                BadgeColumn::make('created_at')->label('Ajouté le')
                    ->dateTime('d-m-Y')
                    ->wrap(),

            ])->defaultSort('visites.created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->user()->name;

                        return $data;
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('visites.created_at', 'desc');
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
            'index' => Pages\ListVisites::route('/'),
            'create' => Pages\CreateVisite::route('/create'),
            'edit' => Pages\EditVisite::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::visites_read()->value,
            PermissionsClass::visites_update()->value,
        ]);
    }
}
