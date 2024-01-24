<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\User;
use Filament\Tables;
use App\Models\Engine;
use Filament\Forms\Get;
use App\Models\Reparation;
use App\Models\Prestataire;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\TypeReparation;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ReparationResource\Pages;
use Filament\Forms\Components\Builder as FilamentBuilder;


class ReparationResource extends Resource
{
    protected static ?string $model = Reparation::class;
    protected static ?string $navigationGroup = 'Flotte automobile';

    protected static ?string $modelLabel = 'Réparations';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments';

    public static function form(Form $form): Form
    {

        return $form

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
                                    ->relationship('typeReparations', 'libelle', fn (Builder $query) => $query->where('state', StatesClass::Activated()->value))
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

                                        // FilamentBuilder\Block::make('Détails')
                                        //     ->icon('heroicon-o-bookmark')
                                        //     ->schema([
                                        //         MarkdownEditor::make('details')
                                        //             ->disableAllToolbarButtons()
                                        //             ->enableToolbarButtons([
                                        //                 'bold',
                                        //                 'bulletList',
                                        //                 'edit',
                                        //                 'italic',
                                        //                 'preview',
                                        //                 'strike',
                                        //             ])
                                        //             ->placeholder('Détails de la révision (maximum 255 caractères)')
                                        //             ->rules(['max:255']),
                                        //     ]),

                                    ])
                                    ->minItems(1)
                                    ->collapsible(),

                                // Repeater::make('infos')
                                //     ->schema([

                                //         Card::make()
                                //             ->schema([
                                //                 Select::make('révisions')
                                //                     ->label("Nature de la réparation")
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

                            MarkdownEditor::make('details')
                                        ->disableAllToolbarButtons()
                                        ->enableToolbarButtons([
                                            // 'bold',
                                            // 'bulletList',
                                            // 'edit',
                                            // 'italic',
                                            // 'preview',
                                            // 'strike',
                                        ])
                                        ->placeholder('Détails de la révision'),

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
                // TextColumn::make('id'),
                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_lancement')
                    ->label('Date d\'envoi en réparation')
                    ->dateTime('d-m-Y'),

                TextColumn::make('date_fin')
                    ->placeholder('-')
                    ->label('Date de retour du véhicule')
                    ->dateTime('d-m-Y'),

                TagsColumn::make('typeReparations.libelle')
                    ->label('Type de la réparation')
                    ->limit(3)
                    ->searchable(),

                TextColumn::make('prestataire')
                    ->searchable(),

                TextColumn::make('name')
                    ->label("Enregistré par")
                    ->alignment('center')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cout_reparation')
                    ->placeholder("-")
                    ->searchable()
                    ->label('Coût de la réparation'),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date_lancement')
                    ->label('Date d\'envoi en réparation')
                    ->form([
                        Grid::make(2)
                            ->schema([

                                DatePicker::make('date_from')
                                    ->label("Du"),

                                DatePicker::make('date_to')
                                    ->label("Au"),

                            ])->columns(1)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_lancement', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_lancement', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (($data['date_from']) && ($data['date_from'])) {
                            return 'Date d\'envoi en réparation:  ' . Carbon::parse($data['date_from'])->format('d-m-Y') . " au " . Carbon::parse($data['date_to'])->format('d-m-Y');
                        }
                        return null;
                    }),

                SelectFilter::make('Prestataire')
                    ->multiple()
                    ->relationship('prestataire', 'nom'),

                SelectFilter::make('Type de la réparation')
                    ->multiple()
                    ->relationship('typeReparations', 'libelle')

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListReparations::route('/'),
            'create' => Pages\CreateReparation::route('/create'),
            'edit' => Pages\EditReparation::route('/{record}/edit'),
        ];
    }


    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::reparation_read()->value,
            PermissionsClass::reparation_update()->value,
        ]);
    }



}


