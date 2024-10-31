<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\ReparationResource;
use App\Models\Reparation;
use App\Models\Role;
use App\Support\Database\PermissionsClass;
use App\Support\Database\ReparationValidationStates;
use App\Support\Database\StatesClass;
use Carbon\Carbon;
use closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Cout de la réparation')
                    ->formatStateUsing( fn($state) => number_format($state, '0', ' ', '.').' FCFA'),

                TextColumn::make('validation_state')
                    ->label('Statut de validation')
                    ->formatStateUsing(function ($state) {
                        // dd($state);
                        if ($state == 'nextValue') {
                            return 'Terminée';
                        } elseif ($state == ReparationValidationStates::Rejete()->value) {

                            return 'Rejetée';

                        } else {
                            $validator = (Role::find($state))->name;

                            return 'En attente de validation de: '.$validator;
                        }

                    })
                    ->color(function ($record) {
                        if ($record->validation_state == ReparationValidationStates::Rejete()->value) {
                            return 'danger';
                        } elseif ($record->validation_state == 'nextValue') {
                            return 'success';
                        } else {
                            return 'primary';
                        }
                    })
                    ->weight('bold'),

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
                // FilamentExportHeaderAction::make('export'),

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
        return fn (Reparation $record): string => url('reparations/'.$record->id);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->select('reparations.*')
            ->where('reparations.state', '<>', StatesClass::Deactivated()->value);

    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return null;
    }
}
