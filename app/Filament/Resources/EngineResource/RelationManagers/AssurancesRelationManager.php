<?php

namespace App\Filament\Resources\EngineResource\RelationManagers;

use App\Models\Assurance;
use App\Models\User;
use App\Support\Database\StatesClass;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class AssurancesRelationManager extends RelationManager
{
    protected static string $relationship = 'assurances';

    protected static ?string $recordTitleAttribute = 'assurance_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                DatePicker::make('date_debut')
                    ->before('date_fin')
                    ->label('Date initiale')
                    ->required(),

                DatePicker::make('date_fin')
                    ->label("Date d'expiration")
                    ->required(),

                Hidden::make('user_id')
                    ->default(auth()->user()->name),

                Hidden::make('updated_at_user_id')
                    ->default(auth()->user()->id),

                Card::make()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Ajoutée le:')
                            ->content(fn (Assurance $record): ?string => $record->created_at),

                        Placeholder::make('updated_at')
                            ->label('Mise à jour:')
                            ->content(fn (Assurance $record): ?string => $record->updated_at),

                        Placeholder::make('user_id')
                            ->label('Enregistrée par:')
                            ->content(fn (Assurance $record): ?string => User::find($record->user_id)?->name),

                        Placeholder::make('updated_at_user_id')
                            ->label('Modifiée en dernier par:')
                            ->content(fn (Assurance $record): ?string => User::find($record->updated_at_user_id)?->name),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Assurance $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id'),
                BadgeColumn::make('date_debut')
                    ->color('success')
                    ->label('Date initiale')
                    ->sortable()
                    ->dateTime('d-m-Y')
                    ->alignment('center'),
                BadgeColumn::make('date_fin')
                    ->color('success')
                    ->label("Date d'expiration")
                    ->sortable()
                    ->dateTime('d-m-Y')
                    ->alignment('center'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->leftjoin('users', 'assurances.user_id', '=', 'users.id')
            ->join('engines', 'assurances.engine_id', '=', 'engines.id')
            ->select('engines.plate_number', 'assurances.*', 'users.name')
            ->whereNull('assurances.deleted_at')
            ->where('engines.state', '=', StatesClass::Activated()->value)
            ->where('assurances.state', '=', StatesClass::Activated()->value);
    }
}
