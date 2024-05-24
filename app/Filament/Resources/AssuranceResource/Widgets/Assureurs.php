<?php

namespace App\Filament\Resources\AssuranceResource\Widgets;

use App\Models\PoliceAssurance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class Assureurs extends BaseWidget
{
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    protected function getTableQuery(): Builder
    {
        return PoliceAssurance::join('fournisseur', 'polices_assurances.assureur_id', 'fournisseur.code_fr')
            ->select(['raison_social_fr', 'code_fr', 'numero_police', 'id'])
            ->whereIn('code_fr', [97, 9, 10]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('raison_social_fr')
                ->label("Nom de l'assureur"),

            TextInputColumn::make('numero_police')
                ->label('N° police assurance')
                ->placeholder('-'),
        ];
    }
}
