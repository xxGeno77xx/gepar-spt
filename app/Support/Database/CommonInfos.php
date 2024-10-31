<?php

namespace App\Support\Database;

use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;

class CommonInfos
{
    public static function PlaceholderCard()
    {
        $userRole = auth()->user()->hasRole(RolesEnum::Super_administrateur()->value);

        if ($userRole) {
            return
                Card::make()
                    ->schema([

                        Grid::make(4)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Ajouté le:')
                                    ->content(fn ($record): ?string => $record->created_at->format('d-m-Y H:i:s')),

                                Placeholder::make('updated_at')
                                    ->label('Mise à jour:')
                                    ->content(fn ($record): ?string => $record->updated_at->format('d-m-Y H:i:s')),

                                placeholder::make('user_id')
                                    ->label('Enregistré par:')
                                    ->content(fn ($record): ?string => User::find($record->user_id)?->name),

                                placeholder::make('updated_at_user_id')
                                    ->label('Modifi(é) en dernier par:')
                                    ->content(fn ($record): ?string => User::find($record->updated_at_user_id)?->name),

                            ]),

                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn ($record) => $record === null);
        } else {
            return Placeholder::make('created_at')
                ->label('Ajouté le:')
                ->content(fn ($record): ?string => $record?->created_at->format('d-m-Y H:i:s'));
        }

    }
}
