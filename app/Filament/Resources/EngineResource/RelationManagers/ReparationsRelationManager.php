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
        return $form

            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Select::make('engine_id')
                                    ->label('Numéro de plaque')
                                    ->options(
                                        Engine::where('engines.state', '<>', StatesClass::Deactivated()->value)
                                            ->pluck('plate_number', 'id')
                                    )
                                    ->searchable()
                                    ->required(),

                                Select::make('prestataire_id')
                                    ->label('Prestataire')
                                    ->options(Prestataire::pluck('raison_social_fr', 'code_fr'))
                                    ->searchable()
                                    ->preload(true)
                                    ->required(),

                                DatePicker::make('date_lancement')
                                    ->label("Date d'envoi en réparation")
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label('Date de retour du véhicule')
                                    ->afterOrEqual('date_lancement'),

                            ])->columns(2),

                        Section::make('Travaux à faire')
                            ->schema([

                                Select::make('révisions')
                                    ->label('Type de la réparation')
                                    ->relationship('typeReparations', 'libelle')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(true)
                                    ->required(),

                                FilamentBuilder::make('infos')
                                    ->label('Achats')
                                    ->blocks([
                                        FilamentBuilder\Block::make('Achat')
                                            ->icon('heroicon-o-adjustments')
                                            ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                        TextInput::make('Designation'),

                                                        TextInput::make('nombre')
                                                            ->numeric()
                                                            ->minValue(1),

                                                        TextInput::make('Prix_unitaire')
                                                            ->numeric()
                                                            ->suffix('FCFA')
                                                            ->minValue(1)
                                                            ->reactive()
                                                            ->integer()
                                                            ->afterStateUpdated(fn ($state, callable $set, $get) => $set('montant', Str::slug($state) * $get('nombre'))),

                                                        TextInput::make('montant')
                                                            ->suffix('FCFA')
                                                            ->numeric()
                                                            ->integer()
                                                            ->disabled()
                                                            ->dehydrated(true),
                                                    ]),

                                            ]),

                                        FilamentBuilder\Block::make('Détails')
                                            ->icon('heroicon-o-bookmark')
                                            ->schema([
                                                MarkdownEditor::make('details')
                                                    ->disableAllToolbarButtons()
                                                    ->enableToolbarButtons([
                                                        'bold',
                                                        'bulletList',
                                                        'edit',
                                                        'italic',
                                                        'preview',
                                                        'strike',
                                                    ])
                                                    ->placeholder('Détails de la révision (maximum 255 caractères)')
                                                    ->rules(['max:255']),
                                            ]),

                                    ])
                                    ->collapsible(),
                            ]),

                        TextInput::make('cout_reparation')
                            ->label('Cout total de la révision')
                            ->numeric()
                            ->required(),

                        FileUpload::make('facture')
                            ->enableDownload()
                            ->enableOpen(),

                        Hidden::make('user_id')->default(auth()->user()->id),

                        Hidden::make('updated_at_user_id')->default(auth()->user()->id),

                        CommonInfos::PlaceholderCard(),

                    ]),

            ]);

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
                FilamentExportHeaderAction::make('export')
               
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(! auth()->user()->hasPermissionTo(PermissionsClass::reparation_update()->value)),
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
