<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\Engine;
use App\Models\Assurance;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use App\Support\Database\CommonInfos;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use App\Support\Database\PermissionsClass;
use App\Filament\Resources\AssuranceResource\Pages;



class AssuranceResource extends Resource
{
    protected static ?string $model = Assurance::class;
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

   
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('engine_id')
                            ->label("Numéro de plaque")
                            ->options(Engine::select(['plate_number',"id"])
                                        ->where('engines.state',StatesClass::Activated())
                                        ->get()->pluck('plate_number',"id")
                                )
                            ->searchable()
                            ->required(),

                        DatePicker::make('date_debut')
                            ->before('date_fin')
                            ->label("Date initiale")
                            ->required(),
                            
                        DatePicker::make('date_fin')
                            ->label("Date d'expiration")
                            ->required(),

                        Hidden::make('user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),

                        Hidden::make('updated_at_user_id')
                            ->default(auth()->user()->id)
                            ->disabled(),
                            
                    ])
                    ->columnSpan(['lg' => fn (?Assurance $record) => $record === null ? 3 : 2]),

                    CommonInfos::PlaceholderCard(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_number')
                    ->label('Numéro de plaque')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_debut')
                    ->dateTime('d-m-Y')
                    ->searchable(),

                TextColumn::make('date_fin')
                    ->dateTime('d-m-Y')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Enregistré par')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('created_at')->label("Ajouté le")
                     ->dateTime('d-m-Y')
                     ->wrap(),
            ])
            ->defaultSort('created_at','desc')

            ->filters([
                //
            ])

            ->actions([
                ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->user()->name;
                        return $data;
                    }),
                EditAction::make(),
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
            'index' => Pages\ListAssurances::route('/'),
            'create' => Pages\CreateAssurance::route('/create'),
            'edit' => Pages\EditAssurance::route('/{record}/edit'),
        ];
    }   
    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::assurances_read()->value,
            PermissionsClass::assurances_update()->value,
        ]);
    }

   
}

