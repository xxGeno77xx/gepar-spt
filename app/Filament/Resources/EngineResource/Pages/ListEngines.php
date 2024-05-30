<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use App\Support\Database\PermissionsClass;
use App\Support\Database\StatesClass;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListEngines extends ListRecords
{
    protected static ?string $title = 'Engins';

    protected static string $resource = EngineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajouter un engin'),
        ];

    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
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
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->select(
                'engines.*',
                'centre.sigle_centre',
                'marques.nom_marque',
                'marques.logo',
                'assurances.date_fin as date_fin',
                'visites.date_expiration as date_expiration',
                /*'users.name', /*'chauffeurs.name as chauffeur'*/
            )
            ->groupBy(
                'engines.id',
                'date_expiration',
                'engines.tvm_mail_sent',
                'date_fin',
                'engines.modele_id',
                'engines.power',
                'engines.distance_parcourue',
                'engines.departement_id',
                'engines.price',
                'engines.circularization_date',
                'engines.date_aquisition',
                'engines.plate_number',
                'modeles.nom_modele',
                'engines.type_id',
                'engines.car_document',
                'engines.carburant_id',
                'engines.assurances_mail_sent',
                'engines.visites_mail_sent',
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.carosserie',
                'engines.pl_ass',
                'engines.matricule_precedent',
                'engines.poids_total_en_charge',
                'engines.poids_a_vide',
                'engines.poids_total_roulant',
                'engines.charge_utile',
                'engines.largeur',
                'engines.surface',
                'engines.couleur',
                'engines.date_cert_precedent',
                'engines.kilometrage_achat',
                'engines.numero_carte_grise',
                'engines.user_id',
                'engines.updated_at_user_id',
                'engines.deleted_at',
                'engines.created_at',
                'engines.updated_at',
                'sigle_centre',
                'nom_modele',
                'nom_marque',
                'logo',
                'remainder'
            );
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        $userPermission = $user->hasAnyPermission([PermissionsClass::engines_read()->value]);

        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
}
