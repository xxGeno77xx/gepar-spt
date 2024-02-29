<?php

namespace App\Filament\Resources\OrdreDeMissionResource\Pages;

use App\Filament\Resources\OrdreDeMissionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdreDeMission extends EditRecord
{
    protected static string $resource = OrdreDeMissionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('printC')
                ->label('PDF (couleur)')
                ->color('success')
                ->icon('heroicon-o-document-download')
                ->url(fn () => route('couleur', $this->record)) //this to orders
                ->openUrlInNewTab(),

            Actions\Action::make('printNB')
                ->label('PDF (Noir & Blanc)')
                ->color('success')
                ->icon('heroicon-o-document-download')
                ->url(fn ($record) => route('pdfNoirBlanc', $this->record)) //this to orders
                ->openUrlInNewTab(),
        ];
    }
}
