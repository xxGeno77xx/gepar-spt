<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use closure;
use Carbon\Carbon;
use Filament\Tables;
use App\Models\Engine;
use App\Models\Reparation;
use App\Models\Prestataire;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ReparationResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Builder as FilamentBuilder;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;

class ReparationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reparations';

    protected static ?string $title = 'Réparations';

    protected static ?string $recordTitleAttribute = 'réparation';

    public static function form(Form $form): Form
    {
        return ReparationResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('date_lancement')
                    ->label('Date d\'envoi en réparation')
                    ->dateTime('d-m-Y'),

                TextColumn::make('date_fin')
                    ->placeholder('-')
                    ->label('Date de retour du véhicule')
                    ->dateTime('d-m-Y'),

                // TextColumn::make('details')
                //     ->limit(15)
                //     ->searchable(),

                TagsColumn::make('typeReparations.libelle')
                    ->label('Type de la réparation')
                    ->limit(3)
                    ->searchable(),

                TextColumn::make('cout_reparation')
                    ->placeholder('-')
                    ->label('Cout de la réparation'),
            ])
            ->filters([
                Filter::make('date_lancement')
                    ->label('Plage de recherche')
                    ->form([
                        Placeholder::make("Date d'envoi en réparation"),
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_lancement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_lancement', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date d\'envoi en réparation:  '.Carbon::parse($data['date_from'])->format('d-m-Y').' au '.Carbon::parse($data['date_to'])->format('d-m-Y');
                        }

                        return null;
                    }),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                FilamentExportHeaderAction::make('export'),

            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->hidden(! auth()->user()->hasPermissionTo(PermissionsClass::reparation_update()->value)),
                Tables\Actions\ViewAction::make()
                    ->hidden(! auth()->user()->hasPermissionTo(PermissionsClass::reparation_read()->value)),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('reparations.created_at', 'desc');

    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Reparation $record): string => url('reparations/'.$record->id.'/edit');
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->select('reparations.*')
            ->where('reparations.state', StatesClass::Activated()->value);

    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return null;
    }
}
