<?php

namespace App\Filament\Resources\PlanningVoyageResource\Widgets;

use Filament\Pages\Actions\Action;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class PlanningList extends Widget
{
    protected static string $view = 'filament.resources.planning-voyage-resource.widgets.planning-list';

    protected int|string|array $columnSpan = 'full';

    public ?Model $record = null;

    protected $listeners = [
        'updateList' => 'refresh',
    ];

    public function refresh()
    {

    }

    public function getViewData(): array
    {

        $collection = $this->record->order;

        foreach ($collection as $coll) {

            $data[] = collect($coll);
        }

        return [
            'data' => $data,
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('settings'),
        ];
    }
}
