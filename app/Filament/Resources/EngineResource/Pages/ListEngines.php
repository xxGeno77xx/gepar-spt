<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Models\Role;
use Filament\Pages\Actions;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Support\Database\RolesEnum;
use Filament\Forms\Components\Grid;
use App\Support\Database\StatesClass;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EngineResource;
use App\Support\Database\PermissionsClass;
use Database\Seeders\RolesPermissionsSeeder;

class ListEngines extends ListRecords
{
    protected static ?string $title = 'Engins';

    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label('Fiche de consommation')
                ->form([
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('Date début')
                                ->required(),
                            DatePicker::make('end_date')
                                ->label('Date fin')
                                ->required()
                                ->after('start_date')
                        ])
                ])
                
                ->action(function($data) {

                    $totalConsommation = $this->getConsommation($data["start_date"], $data["end_date"])->sum('consommation');

                    $totalMontant = $this->getConsommation($data["start_date"], $data["end_date"])->sum('montant');

                    $nombreTotalPrises = $this->getConsommation($data["start_date"], $data["end_date"])->sum('nombreprises');
                    
                   return  Self::stream('consommationForDept',
                     [
                        'data'                  => $this->getConsommation($data["start_date"], $data["end_date"]),
                        'startDate'             => $data["start_date"],
                        'endDate'               => $data["end_date"],
                        'totalConsommation'     => $totalConsommation,
                        'totalMontant'          => $totalMontant,
                        'nombreTotalPrises'     => $nombreTotalPrises,
                     ],
                     "consommation");

                }  )
                ->icon('heroicon-o-printer'),

            Actions\CreateAction::make()
                ->label('Ajouter un engin'),
        ];

    }

    protected function getConsommation($startDate, $endDate)
    {
        return $this->getTableQuery()
        ->join("consommation_carburants", "consommation_carburants.engine_id", "engines.id")
        ->join('carburants', 'carburants.id', 'consommation_carburants.carburant_id')
        ->join('centre', 'engines.departement_id', 'centre.code_centre')

        ->select([
            'engines.plate_number',
             'carburants.type_carburant',
             'centre.sigle_centre',
            DB::raw('SUM(consommation_carburants.montant_total) as montant'),
            DB::raw('SUM(consommation_carburants.quantite) as consommation'),
            DB::raw('COUNT(consommation_carburants.quantite) as nombrePrises'),
            ])
            ->groupBy(['engines.plate_number', 'carburants.type_carburant', 'centre.sigle_centre'])
            ->whereBetween('consommation_carburants.date_prise', [$startDate, $endDate])
            ->get();
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {

        $loggedUser = auth()->user();

        $seeAll = [
            RolesEnum::Dpl()->value,
            RolesEnum::Chef_parc()->value,
            RolesEnum::Super_administrateur()->value,
            RolesEnum::Chef_DPL()->value,
        ];
        $specific = Role::whereNotIn('name', $seeAll)->pluck('name')->toArray();

        if (!$loggedUser->hasAnyRole($specific)) {

            return $this->seeAllQuery();

        } elseif ($loggedUser->hasRole(RolesEnum::Dpl()->value) && $loggedUser->hasRole(RolesEnum::Chef_division()->value)) {

            return $this->seeAllQuery();

        } else
            return $this->specificQuery();
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_read()->value, PermissionsClass::Engines_update()->value, PermissionsClass::Engines_create()->value]);

        abort_if(!$userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    public function specificQuery()
    {
        return $this->baseQuery()->where('engines.departement_id', auth()->user()->departement_id);
    }

    public function seeAllQuery()
    {
        return $this->baseQuery();
    }

    public function baseQuery()
    {
        return static::getResource()::getEloquentQuery()
            ->leftJoin('assurances', function ($join) {
                $join->on('engines.id', '=', 'assurances.engine_id')
                    ->where('assurances.state', StatesClass::Activated()->value)
                    ->whereRaw('assurances.id = (SELECT MAX(id) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [StatesClass::Activated()->value]);
            })
            ->leftJoin('visites', function ($join) {
                $join->on('engines.id', '=', 'visites.engine_id')
                    ->where('visites.state', StatesClass::Activated()->value)
                    ->whereRaw('visites.id = (SELECT MAX(id) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [StatesClass::Activated()->value]);
            })
            // ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('marques', 'engines.marque_id', '=', 'marques.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->select(
                'engines.id',
                'engines.plate_number',
                'marques.logo',
                'assurances.date_fin as date_fin',
                'visites.date_expiration as date_expiration',
                'engines.state',
                'engines.departement_id'
            );
    }



    public static function stream(string $viewName, array $data, string $fileName)
    {

        $pdf = Pdf::loadView($viewName, $data);

        return response()->streamDownload(function () use ($pdf) {

            echo $pdf->stream();

            Notification::make('stream')
                ->title(__('Téléchargement'))
                ->body(__('La fiche a été téléchargée'))
                ->icon('heroicon-o-printer')
                ->send();

        }, $fileName.'.pdf');

    }
}
