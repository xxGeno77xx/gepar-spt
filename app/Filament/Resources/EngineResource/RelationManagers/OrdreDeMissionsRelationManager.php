<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Models\Chauffeur;
use App\Models\OrdreDeMission;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class OrdreDeMissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'ordreDeMissions';

    protected static ?string $title = 'Ordres de mission';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_ordre')
                    ->label("Numéro d'ordre"),

                BadgeColumn::make('chauffeur_id')
                    ->label('Chauffeur')
                    ->formatStateUsing(fn ($state): string => Chauffeur::find($state)->name)
                    ->color('success'),

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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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

    // protected function getTableQuery(): Builder
    // {

    //     $query =
    //     return parent::getTableQuery()
    //         ->join('engines', 'assurances.engine_id', '=', 'engines.id')
    //         ->select('engines.plate_number', 'assurances.*')
    //         ->whereNull('assurances.deleted_at')
    //         ->where('assurances.state', '=', StatesClass::Activated()->value);
    // }
}
