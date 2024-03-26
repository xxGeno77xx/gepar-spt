<?php

namespace App\Filament\Resources\PlanningVoyageResource\Pages;

use App\Filament\Resources\PlanningVoyageResource;
use App\Filament\Resources\PlanningVoyageResource\Widgets\PlanningList;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPlanningVoyage extends EditRecord
{
    protected static string $resource = PlanningVoyageResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),

            Action::make('print')
                ->label('Imprimer')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('planningVoyage', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PlanningList::class,
        ];
    }

    public function aftersave()
    {

        $this->emit('updateList');
    }
}
