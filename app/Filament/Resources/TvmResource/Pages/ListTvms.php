<?php

namespace App\Filament\Resources\TvmResource\Pages;

use App\Filament\Resources\TvmResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTvms extends ListRecords
{
    protected static string $resource = TvmResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
