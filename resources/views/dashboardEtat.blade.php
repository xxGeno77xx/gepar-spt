@php

    use App\Models\Engine;
    use App\Models\Type;
    use App\Models\Departement;
    use App\Models\Carburant;
    use App\Models\DistanceParcourue;
    use App\Models\ConsommationCarburant;
    use App\Support\Database\TypesClass;
    use App\Support\Database\CarburantsClass;
    use App\Support\Database\StatesClass;
    use Illuminate\Support\Facades\DB;

    //=======engines and categories====================
    $allEnginesCount = Engine::where('state', '<>', StatesClass::Deactivated()->value)
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->count();

    $deuxRouesIDs = Type::whereIn('nom_type', [
        TypesClass::Transport_a_deux_roues()->value,
        TypesClass::Tricycle_motorises()->value,
    ])->pluck('id');

    $vehiculesAutomobiles = Engine::where('poids_a_vide', '<', 3500)
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();

    $vehiculeUtilitairesLegers = Engine::whereBetween('poids_a_vide', [3500, 12000])
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();

    $vehiculeUtilitairesLourds = Engine::where('poids_a_vide', '>', 12000)
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();

    $transportADeuxRoues = Engine::where(
        'type_id',
        Type::where('nom_type', TypesClass::Transport_a_deux_roues()->value)->first()->id,
    )
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->get();

    $tricyclesMotorises = Engine::where(
        'type_id',
        Type::where('nom_type', TypesClass::Tricycle_motorises()->value)->first()->id,
    )
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->get();

    // ============================== IDs======================

    $vehiculesAutomobilesIDs = $vehiculesAutomobiles->pluck('id')->toArray();
    $vehiculeUtilitairesLegersIDs = $vehiculeUtilitairesLegers->pluck('id')->toArray();
    $vehiculeUtilitairesLourdsIDs = $vehiculeUtilitairesLourds->pluck('id')->toArray();
    $transportADeuxRouesIDs = $transportADeuxRoues->pluck('id')->toArray();
    $tricyclesMotorisesIDs = $tricyclesMotorises->pluck('id')->toArray();

    //==============distances for engines in diffrent categories====================
    $vehiculesAutomobilesDistance = DistanceParcourue::whereIn('engine_id', $vehiculesAutomobilesIDs)
        ->whereYear('date_distance_parcourue', $annee)
        ->sum('distance');

    $vehiculeUtilitairesLourdsDistance = DistanceParcourue::whereIn('engine_id', $vehiculeUtilitairesLourdsIDs)
        ->whereYear('date_distance_parcourue', $annee)
        ->sum('distance');

    $vehiculeUtilitairesLegersDistance = DistanceParcourue::whereIn('engine_id', $vehiculeUtilitairesLegersIDs)
        ->whereYear('date_distance_parcourue', $annee)
        ->sum('distance');

    $transportADeuxRouesDistance = DistanceParcourue::whereIn('engine_id', $transportADeuxRouesIDs)
        ->whereYear('date_distance_parcourue', $annee)
        ->sum('distance');

    $tricyclesMotorisesDistance = DistanceParcourue::whereIn('engine_id', $tricyclesMotorisesIDs)
        ->whereYear('date_distance_parcourue', $annee)
        ->sum('distance');

    //=========================Consommations de carburant par catégorie =========

    $vehiculesAutomobilesConso = ConsommationCarburant::whereIn('engine_id', $vehiculesAutomobilesIDs)
        ->whereYear('created_at', $annee)
        ->sum('quantite');

    $vehiculeUtilitairesLourdsConso = ConsommationCarburant::whereIn('engine_id', $vehiculeUtilitairesLourdsIDs)
        ->whereYear('created_at', $annee)
        ->sum('quantite');

    $vehiculeUtilitairesLegersConso = ConsommationCarburant::whereIn('engine_id', $vehiculeUtilitairesLegersIDs)
        ->whereYear('created_at', $annee)
        ->sum('quantite');

    $transportADeuxRouesConso = ConsommationCarburant::whereIn('engine_id', $transportADeuxRouesIDs)
        ->whereYear('created_at', $annee)
        ->sum('quantite');

    $tricyclesMotorisesConso = ConsommationCarburant::whereIn('engine_id', $tricyclesMotorisesIDs)
        ->whereYear('created_at', $annee)
        ->sum('quantite');

    //===collections===============
    $consoCollection = [
        $vehiculesAutomobilesConso,
        $vehiculeUtilitairesLourdsConso,
        $vehiculeUtilitairesLegersConso,
        $transportADeuxRouesConso,
        $tricyclesMotorisesConso,
    ];

    $distancesCollection = [
        $vehiculesAutomobilesDistance,
        $vehiculeUtilitairesLourdsDistance,
        $vehiculeUtilitairesLegersDistance,
        $transportADeuxRouesDistance,
        $tricyclesMotorisesDistance,
    ];

    $departementsWithEnginesIDs = Engine::whereNotNull('departement_id')
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->distinct()
        ->pluck('departement_id')
        ->toArray();

    $enginesPerDepartment = Engine::whereNotNull('departement_id')
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->selectRaw('COUNT(engines.id) as count')
        ->groupBy('departement_id')
        ->pluck('count')
        ->toArray();

    $typesCarburantWithEngineRecords = Engine::whereDate('created_at', '<=', $annee . '/12/31/')
        ->distinct()
        ->pluck('carburant_id')
        ->toArray();

    $enginesPerCarburant = Engine::whereIn('carburant_id', $typesCarburantWithEngineRecords)
        ->whereDate('created_at', '<=', $annee . '/12/31/')
        ->selectRaw('COUNT(engines.id) as count')
        ->groupBy('carburant_id')
        ->pluck('count')
        ->toArray();

    $categoriesEngins = [
        'Véhicules automobiles (4 roues dont le poids < 3.5 t)' => $vehiculesAutomobiles->count(),
        'Véhicules utilitaires léger (4 roues dont le poids < 12 t)' => $vehiculeUtilitairesLegers->count(),
        'Véhicules utilitaires lourds (4 roues dont le poids > 12 t)' => $vehiculeUtilitairesLourds->count(),
        'Transports à 2 roues' => $transportADeuxRoues->count(),
        'Tricycles motorisés' => $tricyclesMotorises->count(),
    ];

    // ===========================individuals===============================================================
    $vehiculesAutomobilesIndividuels = Engine::whereIn('engines.id', $vehiculesAutomobilesIDs)
        ->leftjoin('distance_parcourues', function ($join) use ($annee) {
            $join
                ->on('distance_parcourues.engine_id', 'engines.id')
                ->select('distance')
                ->whereYear('date_distance_parcourue', $annee);
        })
        ->join('consommation_carburants', 'consommation_carburants.engine_id', 'engines.id')
        ->rightjoin('consommation_carburants', function($join) use ($annee){
            $join->on( 'consommation_carburants.engine_id', 'engines.id')
                ->whereYear('consommation_carburants.created_at', $annee) ;
        })
        ->join('carburants', 'carburants.id', 'engines.carburant_id')
        ->select(
            'engines.plate_number',
            'distance',
            DB::raw('SUM(consommation_carburants.quantite) as total_quantite'),
            'type_carburant',
        )
        ->groupBy('engines.id', 'engines.plate_number', 'distance', 'type_carburant')
        ->get();


        $vehiculeUtilitairesLegersIndividuels = Engine::whereIn('engines.id', $vehiculeUtilitairesLegersIDs)
        ->leftjoin('distance_parcourues', function ($join) use ($annee) {
            $join
                ->on('distance_parcourues.engine_id', 'engines.id')
                ->select('distance')
                ->whereYear('date_distance_parcourue', $annee);
        })
        ->join('consommation_carburants', 'consommation_carburants.engine_id', 'engines.id')
        ->rightjoin('consommation_carburants', function($join) use ($annee){
            $join->on( 'consommation_carburants.engine_id', 'engines.id')
                ->whereYear('consommation_carburants.created_at', $annee) ;
        })
        ->join('carburants', 'carburants.id', 'engines.carburant_id')
        ->select(
            'engines.plate_number',
            'distance',
            DB::raw('SUM(consommation_carburants.quantite) as total_quantite'),
            'type_carburant',
        )
        ->groupBy('engines.id', 'engines.plate_number', 'distance', 'type_carburant')
        ->get();


        $vehiculeUtilitairesLourdsIndividuels = Engine::whereIn('engines.id', $vehiculeUtilitairesLourdsIDs)
        ->leftjoin('distance_parcourues', function ($join) use ($annee) {
            $join
                ->on('distance_parcourues.engine_id', 'engines.id')
                ->select('distance')
                ->whereYear('date_distance_parcourue', $annee);
        })
        ->join('consommation_carburants', 'consommation_carburants.engine_id', 'engines.id')
        ->rightjoin('consommation_carburants', function($join) use ($annee){
            $join->on( 'consommation_carburants.engine_id', 'engines.id')
                ->whereYear('consommation_carburants.created_at', $annee) ;
        })
        ->join('carburants', 'carburants.id', 'engines.carburant_id')
        ->select(
            'engines.plate_number',
            'distance',
            DB::raw('SUM(consommation_carburants.quantite) as total_quantite'),
            'type_carburant',
        )
        ->groupBy('engines.id', 'engines.plate_number', 'distance', 'type_carburant')
        ->get();



  $transportADeuxRouesIndividuels = Engine::whereIn('engines.id', $transportADeuxRouesIDs)
        ->leftjoin('distance_parcourues', function ($join) use ($annee) {
            $join
                ->on('distance_parcourues.engine_id', 'engines.id')
                ->select('distance')
                ->whereYear('date_distance_parcourue', $annee);
        })
        ->join('consommation_carburants', 'consommation_carburants.engine_id', 'engines.id')
        ->rightjoin('consommation_carburants', function($join) use ($annee){
            $join->on( 'consommation_carburants.engine_id', 'engines.id')
                ->whereYear('consommation_carburants.created_at', $annee) ;
        })
        ->join('carburants', 'carburants.id', 'engines.carburant_id')
        ->select(
            'engines.plate_number',
            'distance',
            DB::raw('SUM(consommation_carburants.quantite) as total_quantite'),
            'type_carburant',
        )
        ->groupBy('engines.id', 'engines.plate_number', 'distance', 'type_carburant')
        ->get();


        $tricyclesMotorisesIndividuels = Engine::whereIn('engines.id', $tricyclesMotorisesIDs)
        ->whereDate('engines.created_at', '<=', $annee . '/12/31/')
        ->leftjoin('distance_parcourues', function ($join) use ($annee) {
            $join
                ->on('distance_parcourues.engine_id', 'engines.id')
                ->select('distance')
                ->whereYear('date_distance_parcourue', $annee);
        })
        ->rightjoin('consommation_carburants', function($join) use ($annee){
            $join->on( 'consommation_carburants.engine_id', 'engines.id')
                ->whereYear('consommation_carburants.created_at', $annee) ;
        })

        ->join('carburants', 'carburants.id', 'engines.carburant_id')
        ->select(
            'engines.plate_number',
            'distance',
            DB::raw('SUM(consommation_carburants.quantite) as total_quantite'),
            'type_carburant',
        )
        ->whereYear('consommation_carburants.created_at', $annee) 
        ->groupBy('engines.id', 'engines.plate_number', 'distance', 'type_carburant')
        ->get();

@endphp


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Situation annuelle {{now()->format("d-m-Y")}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .invoice-header {
            background-color: #0099cc;
            color: white;
            padding: 10px;
        }

        .invoice-header h1 {
            margin: 0;
            padding: 0;
        }

        .invoice-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-details td,
        .invoice-details th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .invoice-details th {
            background-color: #0099cc;
            color: white;
        }

        .invoice-details tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .invoice-details tbody tr:hover {
            background-color: #ddd;
        }

        .total {
            background-color: #0099cc;
            color: white;
            text-align: right;
            padding-right: 8px;
        }

        .total-value {
            text-align: right;
            padding-right: 8px;
        }

        h6 {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="invoice-header">
        <h2>Situation globale du parc automobile de la
            SPT en {{ $annee }}
        </h2>
    </div>
    {{-- <table>
        <tr>
            <td>
                <strong>Adresse postale</strong><br>
                Code postal, Ville
            </td>
            <td>
                <strong>Tél. :</strong> Numéro de téléphone<br>
                <strong>Fax :</strong> Numéro de télécopie
            </td>
            <td>
                <strong>Adresse de courrier</strong><br>
                Site web
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Facturer à :</strong> Contoso, Ltd<br>
                Adresse : 123, rue des Écoles<br>
                Bordeaux, Aquitaine 098765
            </td>
            <td>
                <strong>Téléphone :</strong> 05 46 20 40 88<br>
                <strong>Télécopie :</strong> 05 22 33 44 05<br>
                <strong>Adresse e-mail :</strong> xyz@example.com
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Objet de la facture :</strong> Projet 2
            </td>
            <td>
                <strong>N° Facture :</strong> 3-456-2<br>
                <strong>Date de facturation :</strong> 10/06/2024
            </td>
        </tr>
    </table> --}}

    <h3>Toutes les catégories</h3>
    <table class="invoice-details">
        <thead>
            <tr>
                <th>Catégorie d'engins</th>
                <th>Distances parcourues</th>
                <th>Consommations de carburant (L)</th>
                <th>Nombre</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categoriesEngins as $key => $categorie)
                <tr>
                    <td>{{ $key }}</td>
                    <td> {{ $distancesCollection[$loop->iteration - 1] }} </td>
                    <td> {{ $consoCollection[$loop->iteration - 1] }} </td>
                    <td> {{ $categorie }} </td>
                    <td>{{ round(($categorie / $allEnginesCount) * 100, 2) }} %</td>
                </tr>
            @endforeach

            <tr class="highlight">
                <td><strong>TOTAL</strong></td>
                <td><strong>-</strong></td>
                <td> - </td>
                <td><strong>{{ $allEnginesCount }}</strong></td>
                <td><strong>100%</strong></td>
            </tr>

        </tbody>
    </table>
    <h6></h6>
    <br>
    <h3>Véhicules automobiles (4 roues dont le poids < 3.5 t)</h3>

    <table class="invoice-details">
        <thead>
            <tr>
                <th>Numéro de plaque</th>
                <th>Distances parcourues</th>
                <th>Consommation(L)</th>
                <th>Carburant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehiculesAutomobilesIndividuels as $key => $engine)
                <tr>
                    <td>{{ $engine->plate_number }}</td>
                    <td> {{ $engine->distance }} </td>
                    <td> {{ $engine->total_quantite }} </td>
                    <td> {{ $engine->type_carburant }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <h6></h6>
    <h3>Véhicules utilitaires légers (4 roues dont le poids < 3.5 t)</h3>

    <table class="invoice-details">
        <thead>
            <tr>
                <th>Numéro de plaque</th>
                <th>Distances parcourues</th>
                <th>Consommation(L)</th>
                <th>Carburant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehiculeUtilitairesLegersIndividuels as $key => $engine)
                <tr>
                    <td>{{ $engine->plate_number }}</td>
                    <td> {{ $engine->distance }} </td>
                    <td> {{ $engine->total_quantite }} </td>
                    <td> {{ $engine->type_carburant }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h6></h6>
<h3>Véhicules utilitaires lourds (4 roues dont le poids < 3.5 t)</h3>

    <table class="invoice-details">
        <thead>
            <tr>
                <th>Numéro de plaque</th>
                <th>Distances parcourues</th>
                <th>Consommation(L)</th>
                <th>Carburant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vehiculeUtilitairesLourdsIndividuels as $key => $engine)
                <tr>
                    <td>{{ $engine->plate_number }}</td>
                    <td> {{ $engine->distance }} </td>
                    <td> {{ $engine->total_quantite }} </td>
                    <td> {{ $engine->type_carburant }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h6></h6>
<h3>Tricycles motorisés</h3>

    <table class="invoice-details">
        <thead>
            <tr>
                <th>Numéro de plaque</th>
                <th>Distances parcourues</th>
                <th>Consommation(L)</th>
                <th>Carburant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tricyclesMotorisesIndividuels as $key => $engine)
                <tr>
                    <td>{{ $engine->plate_number }}</td>
                    <td> {{ $engine->distance }} </td>
                    <td> {{ $engine->total_quantite }} </td>
                    <td> {{ $engine->type_carburant }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h6></h6>
</body>
</html>
