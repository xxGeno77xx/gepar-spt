<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanningVoyageResource\Pages;
use App\Models\Chauffeur;
use App\Models\Departement;
use App\Models\Pays;
use App\Models\PlanningVoyage;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;

class PlanningVoyageResource extends Resource
{
    protected static ?string $model = PlanningVoyage::class;

    protected static ?string $navigationGroup = 'Missions';

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([

                        Card::make()
                            ->schema([

                                Toggle::make('exterieur')
                                    ->label('Extérieur du pays')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-s-paper-airplane')
                                    ->reactive(),

                                Repeater::make('order')
                                    ->label('Ordre de passage')
                                    ->schema([

                                        Grid::make(3)
                                            ->schema([
                                                Select::make('chauffeur')
                                                    ->label('Chauffeur')
                                                    ->options(Chauffeur::pluck('fullname', 'id'))
                                                    ->searchable()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($set, $get) {

                                                        $result = Departement::select('centre.sigle_centre', 'code_centre')
                                                            ->leftjoin('engines', 'engines.departement_id', 'centre.code_centre')
                                                            ->leftjoin('chauffeurs', 'chauffeurs.engine_id', 'engines.id')
                                                            ->where('chauffeurs.state', StatesClass::Activated()->value)
                                                            ->where('chauffeurs.id', $get('chauffeur'))
                                                            ->first();

                                                        $set('affectation', $result->code_centre);
                                                    }),

                                                Select::make('affectation')
                                                    ->options(
                                                        Departement::pluck('sigle_centre', 'code_centre')
                                                    )
                                                    ->disabled()
                                                    ->dehydrated(true),

                                                Select::make('pays')
                                                    ->label('Pays')
                                                    ->options(Pays::pluck('libelle', 'code_pays'))
                                                    ->searchable()
                                                    ->reactive()
                                                    ->visible(fn (callable $get) => $get('../../exterieur') == 1 ? true : false),

                                                Grid::make(2)
                                                    ->schema([
                                                        DatePicker::make('date_debut')
                                                            ->label('Date de début'),

                                                        DatePicker::make('date_fin')
                                                            ->label('Date de fin'),
                                                    ]),

                                            ]),
                                    ])->createItemButtonLabel('Ajouter un chauffeur'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('exterieur')
                    ->label('Destination')
                    ->formatStateUsing(fn ($state) => $state == 1 ? 'Extérieur du pays' : 'Intérieur du pays')
                    ->color(fn ($state) => $state == 1 ? 'primary' : 'success'),

                BadgeColumn::make('created_at')
                    ->label('Date de création')
                    ->date('D d F Y')
                    ->color('primary'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('print')
                    ->label('Imprimer')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (PlanningVoyage $record) => route('planningVoyage', $record))
                    ->openUrlInNewTab(),

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
            'index' => Pages\ListPlanningVoyages::route('/'),
            'create' => Pages\CreatePlanningVoyage::route('/create'),
            'edit' => Pages\EditPlanningVoyage::route('/{record}/edit'),
        ];
    }
}
