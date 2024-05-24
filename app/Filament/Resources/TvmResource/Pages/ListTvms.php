<?php

namespace App\Filament\Resources\TvmResource\Pages;

use App\Filament\Resources\TvmResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTvms extends ListRecords
{
    protected static string $resource = TvmResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join('engines', 'engines.id', 'tvms.engine_id')
            ->select('tvms.*', 'engines.plate_number');
    }
}
