<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use  closure;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Hidden;   
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Builder as FilamentBuilder;



class ReparationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reparations';

    protected static ?string $title = 'Réparations';
    protected static ?string $recordTitleAttribute = 'réparation';

    public static function form(Form $form): Form
    {
        return $form
            // ->schema([
            //     Card::make()
            //     ->schema([
            //         Card::make()
            //             ->schema([
            //                 Select::make('engine_id')
            //                     ->label("Numéro de plaque")
            //                     ->options(Engine::where('engines.state',StatesClass::Activated())
            //                                     ->whereNull('deleted_at')
            //                                     ->pluck('plate_number','id')
            //                          )
            //                     ->searchable()
            //                     ->required(),

            //                 TextInput::make('cout_reparation')
            //                     ->label("Coût de la réparation")
            //                     ->numeric(),   

            //                 DatePicker::make('date_lancement')
            //                     ->label("Date d'envoi en réparation")
            //                     ->required(),
            //                 DatePicker::make('date_fin')
            //                     ->label("Date de retour du véhicule")
            //                     ->afterOrEqual('date_lancement'),
        
            //             ])->columns(2),
                        
            //         Textarea::make('details')
            //             ->placeholder('Détails de la révision')
            //             ->rules(['max:255']),

            //         Hidden::make('user_id')->default(auth()->user()->id),

            //         Hidden::make('updated_at_user_id')->default(auth()->user()->id),

            //         Card::make()
            //         ->schema([
            //             Placeholder::make('created_at')
            //                 ->label('Ajouté le:')
            //                 ->content(fn (Reparation $record): ?string => $record->created_at),

            //             Placeholder::make('updated_at')
            //                 ->label('Mise à jour:')
            //                 ->content(fn (Reparation $record): ?string => $record->updated_at),

            //             placeholder::make('user_id')
            //                 ->label('Enregistré par:')
            //                 ->content(fn(Reparation $record): ?string =>User::find($record->user_id)?->name),

            //             placeholder::make('updated_at_user_id')
            //                 ->label('Modifié en dernier par:')
            //                 ->content(fn(Reparation $record): ?string =>User::find($record->updated_at_user_id)?->name),
                        
            //     ])
            //     ->columnSpan(['lg' => 1])
            //     ->hidden(fn (?Reparation $record) => $record === null),
            //     ]),
            // ]);

            ->schema([
                Card::make()
                    ->schema([
                        Card::make()
                            ->schema([
                                Select::make('engine_id')
                                    ->label("Numéro de plaque")
                                    ->options(
                                        Engine::where('engines.state', '<>', StatesClass::Deactivated())
                                            ->pluck('plate_number', 'id')
                                    )
                                    ->searchable()
                                    ->required(),

                                Select::make('prestataire_id')
                                    ->label("Prestataire")
                                    ->options(Prestataire::pluck('nom', 'id'))
                                    ->searchable()
                                    ->preload(true)
                                    ->required(),

                                DatePicker::make('date_lancement')
                                    ->label("Date d'envoi en réparation")
                                    ->required(),

                                DatePicker::make('date_fin')
                                    ->label("Date de retour du véhicule")
                                    ->afterOrEqual('date_lancement'),

                            ])->columns(2),

                        Section::make('Travaux à faire')
                            ->schema([

                                Select::make('révisions')
                                    ->label("Type de la réparation")
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
                                                            ->afterStateUpdated(fn($state, callable $set, $get) => $set('montant', Str::slug($state) * $get('nombre'))),

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

                                // Repeater::make('infos')
                                //     ->schema([

                                //         Card::make()
                                //             ->schema([
                                //                 Select::make('révisions')
                                //                     ->label("Type de la réparation")
                                //                     ->options(TypeReparation::pluck('libelle', 'id'))
                                //                     ->searchable()
                                //                     ->preload(true)
                                //                     // ->reactive()
                                //                     // ->dehydrated(false)
                                //                     // ->afterStateUpdated(fn($state, callable $set, $get) => $set('Documentation.content' , Str::slug(TypeReparation::where('id',$state)->value('libelle'))))
                                //                     ->required(),

                                //                 // TagsInput::make('Travail_à_faire')
                                //                 //     ->placeholder('Travail à faire')
                                //                 //     ->visible(fn($get): bool => $get('Depenses_supplementaires') == false),

                                //                 // TextInput::make('cout_revision')
                                //                 //     ->label('Cout total de la révision')
                                //                 //     ->numeric()
                                //                 //     ->required()
                                //                 //     ->visible(fn($get): bool => $get('Depenses_supplementaires') == false),

                                //             ])
                                //             ->columns(1),

                                //         Toggle::make('Depenses_supplementaires')
                                //             ->label('Achats à faire')
                                //             ->onColor('success')
                                //             ->offColor('danger')
                                //             ->onIcon('heroicon-o-cash')
                                //             ->reactive(),

                                //         // Section::make('')
                                //         //     ->schema([

                                //                 Repeater::make('Détails des achats')
                                //                     ->schema([

                                //                         TextInput::make('Designation'),

                                //                         TextInput::make('nombre')
                                //                             ->numeric()
                                //                             ->minValue(0),

                                //                         TextInput::make('Prix_unitaire')
                                //                             ->numeric()
                                //                             ->suffix('FCFA')
                                //                             ->minValue(0)
                                //                             ->reactive()
                                //                             ->integer()
                                //                             ->afterStateUpdated(fn($state, callable $set, $get) => $set('montant', Str::slug($state) * $get('nombre'))),

                                //                         TextInput::make('montant')
                                //                             ->suffix('FCFA')
                                //                             ->numeric()
                                //                             ->integer()
                                //                             ->disabled()
                                //                             ->dehydrated(true),
                                //                     ])
                                //                     // ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                                //                     ->orderable(false)
                                //                     ->createItemButtonLabel('Autre achat')
                                //                     ->columns(4)
                                //             // ])
                                //             ->visible(fn($get): bool => $get('Depenses_supplementaires') == true),

                                //     ])
                                //     ->disableItemMovement()
                                //     ->createItemButtonLabel('Ajouter une révision'),

                                // TextInput::make('Main_d\'oeuvre')
                                //     ->label('Main d\'oeuvre'),


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


            TextColumn::make('name')
                ->label("Enregistré par")
                ->alignment('center')
                ->toggleable(isToggledHiddenByDefault: true),
            
            TextColumn::make('cout_reparation')
                ->placeholder("-")
                ->label('Cout de la réparation'),
            ])
            ->filters([ 
                Filter::make('date_lancement')
                        ->label("Plage de recherche")
                        ->form([
                            Placeholder::make("Date d'envoi en réparation"),
                            DatePicker::make('date_from')
                            ->label("Du"),
                            DatePicker::make('date_to')
                            ->label("Au"),
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
                            if (( $data['date_from']) && ($data['date_from'])) {
                                return 'Date d\'envoi en réparation:  ' . Carbon::parse($data['date_from'])->format('d-m-Y')." au ".Carbon::parse($data['date_to'])->format('d-m-Y');
                            }
                            return null;
                        })
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(!auth()->user()->hasPermissionTo(PermissionsClass::reparation_update()->value)),
                Tables\Actions\ViewAction::make()
                    ->hidden(!auth()->user()->hasPermissionTo(PermissionsClass::reparation_read()->value)),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('created_at','desc')
            
            ;
            
    }    

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Reparation $record): string => url('reparations/'.$record->id.'/edit');
    }


    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
        ->join('users','reparations.user_id','users.id')
        ->select('reparations.*','users.name')
        ->where('reparations.state', StatesClass::Activated()->value);
           
    }

    protected function getTableRecordActionUsing(): ?Closure
    {
        return null;
    }

    
}
