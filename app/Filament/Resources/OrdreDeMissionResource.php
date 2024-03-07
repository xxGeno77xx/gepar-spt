<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdreDeMissionResource\Pages;
use App\Models\Chauffeur;
use App\Models\Engine;
use App\Models\OrdreDeMission;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class OrdreDeMissionResource extends Resource
{
    protected static ?string $model = OrdreDeMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Card::make()
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                Select::make('chauffeur_id')
                                    ->label('Chauffeur')
                                    ->options(
                                        Chauffeur::select(['fullname', 'id'])
                                            ->where('Chauffeurs.state', StatesClass::Activated()->value)
                                            ->get()
                                            ->pluck('fullname', 'id')
                                    )
                                    ->required()
                                    ->searchable(),

                                DatePicker::make('date_de_depart')
                                    ->required(),

                                DatePicker::make('date_de_retour')
                                    ->afterOrEqual('date_de_depart')
                                    ->required(),

                                Repeater::make('agents')
                                    ->schema([

                                        Grid::make(2)

                                            ->schema([
                                                TextInput::make('Nom')
                                                ->label('Nom complet')
                                                    ->required(),

                                                TextInput::make('Désignation')
                                                    ->required(),

                                            ]),
                                    ])
                                    ->createItemButtonLabel('Ajouter un agent')
                                    ->columnSpanFull(),

                                Select::make('engine_id')
                                    ->label('Moyen de transport')
                                    ->options(
                                        Engine::select(['plate_number', 'id'])
                                            ->where('engines.state', StatesClass::Activated()->value)
                                            ->get()
                                            ->pluck('plate_number', 'id')
                                    )
                                    ->searchable()
                                    ->required()
                                    ->columnSpanFull(),

                                TagsInput::make('lieu')
                                    ->label('Destination(s)')
                                    ->required()
                                    ->placeholder('Nouvelle destination')
                                    ->columnSpanFull(),

                                TextInput::make('objet_mission')
                                    ->label('Objet de la mission')
                                    ->required()
                                    ->columnSpanFull(),

                                Hidden::make('numero_ordre')
                                    ->default(fn() => OrdreDeMission::orderBy("id", "desc")->first()? OrdreDeMission::orderBy("id", "desc")->first()->id + 1 : 1), //generate the number
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ordre')
                    ->label("Numéro d'ordre"),

                TextColumn::make('chauffeur')
                    ->label('Chauffeur'),

                BadgeColumn::make('date_de_depart')
                    ->date('d-m-Y')
                    ->color('primary'),

                BadgeColumn::make('date_de_retour')
                    ->date('d-m-Y')
                    ->color('danger'),

                BadgeColumn::make('objet_mission')
                    ->limit(25)
                    ->color('success')
                    ->label('Objet de la mission'),

                BadgeColumn::make('lieu')
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('printC')
                        ->label('PDF (couleur)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn (OrdreDeMission $record) => route('couleur', $record)) //this to orders
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('printNB')
                        ->label('PDF (Noir & Blanc)')
                        ->color('success')
                        ->icon('heroicon-o-document-download')
                        ->url(fn (OrdreDeMission $record) => route('pdfNoirBlanc', $record)) //this to orders
                        ->openUrlInNewTab(),
                ]),

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
            'index' => Pages\ListOrdreDeMissions::route('/'),
            'create' => Pages\CreateOrdreDeMission::route('/create'),
            'edit' => Pages\EditOrdreDeMission::route('/{record}/edit'),
        ];
    }
}
