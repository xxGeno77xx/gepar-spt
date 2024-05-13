<?php

namespace App\Filament\Resources\TvmResource\Pages;

use App\Filament\Resources\TvmResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTvm extends EditRecord
{
    protected static string $resource = TvmResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
