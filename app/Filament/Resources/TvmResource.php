<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Tvm;
use Filament\Forms;
use Filament\Tables;
use App\Models\Engine;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TvmResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TvmResource\RelationManagers;

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
                    ->columns(3)
                    ->schema([

                        DatePicker::make('date_debut')
                            ->before('date_fin')
                            ->label('Date initiale')
                            ->reactive()
                            ->afterStateUpdated(fn($set, $get) => $set("date_fin",  Carbon::parse($get("date_debut"))->addYear()))
                            ->required(),

                        DatePicker::make('date_fin')
                            ->label("Date d'expiration")
                            ->required(),

                        TextInput::make("reference")
                            ->required(),

                        Select::make('engine_id')
                        ->label('Engins')
                        ->options(Engine::pluck("plate_number", "id"))
                        ->preload()
                        ->searchable()
                        ->required()
                            ->visibleOn("edit"),

                    TextInput::make("prix")
                    ->numeric()
                        ->visibleOn("edit"),

                        Repeater::make("engins_prix")
                        ->disabledOn("edit")
                        ->visibleOn("create")
                        ->createItemButtonLabel('Ajouter un engin')
                        ->columnSpanFull()
                        
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        Select::make('engine_id')
                                        ->label('Engins')
                                        ->options(Engine::pluck("plate_number", "id"))
                                        ->preload()
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ,
    
                                    TextInput::make("prix")->numeric(),
                                    ])

                               
                            ]),
                        Hidden::make('user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),

                        Hidden::make('updated_at_user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("reference")
                    ->label("Référence"),

                TextColumn::make("date_debut")
                    ->date("d M Y"),

                TextColumn::make("date_fin")
                    ->date("d M Y"),

                TextColumn::make("reference")
                    ->label("Référence"),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTvms::route('/'),
            'create' => Pages\CreateTvm::route('/create'),
            'edit' => Pages\EditTvm::route('/{record}/edit'),
        ];
    }


}
