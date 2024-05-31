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
@endphp



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nombre total de voitures en 2017</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /*background-color: #f9f9f9;*/
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .title {
            text-align: left;
        }

        .subtitle {
            color: rgb(0, 55, 235);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .highlight {
            background-color: #ffa600;
        }
    </style>
</head>

<body>
    <div class="title">
        <h1>Situation globale du parc automobile de la SPT</h1>
        <h2>1. Engins</h2>
        <h3 class="subtitle">1a. Nombre total d'engins en {{ now()->format('Y') }}</h3>
    </div>
    <p>Le tableau ci-dessous donne le nombre total d'engins par département.</p>
    <table>
        <thead>
            <tr>
                <th>DEPARTEMENT</th>
                <th>NOMBRE</th>
                <th>POURC.</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($departementsWithEnginesIDs as $key => $departementId)
                <tr>
                    <td>{{ Departement::find($departementId)->sigle_centre }}</td>
                    <td>{{ $enginesPerDepartment[$key] }}</td>
                    <td>{{ round(($enginesPerDepartment[$key] / $allEnginesCount) * 100, 2) }} %</td>
                </tr>
            @endforeach


            <tr class="highlight">
                <td><strong>TOTAL</strong></td>
                <td><strong>{{ $allEnginesCount }}</strong></td>
                <td><strong>100%</strong></td>
            </tr>
            <tr>

            </tr>
        </tbody>
    </table>

    <div class="title">
        <br>
        <h3 class="subtitle">1b. Carburants utilisés</h3>
    </div>

    <p>Cette partie traite des différents types de véhicules en fonction du type de carburant.</p>
    <table>
        <thead>
            <tr>
                <th>CARBURANT</th>
                <th>KILOMETRAGE</th>
                <th>NOMBRE</th>
                <th>POURC.</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($typesCarburantWithEngineRecords as $key => $carburantID)
                <tr>
                    <td>{{ Carburant::find($carburantID)->type_carburant }}</td>
                    <td><strong>-</strong></td>
                    <td>{{ $enginesPerCarburant[$key] }}</td>
                    <td>{{ round(($enginesPerCarburant[$key] / $allEnginesCount) * 100, 2) }} %</td>
                </tr>
            @endforeach


            <tr class="highlight">
                <td><strong>TOTAL</strong></td>
                <td><strong>-</strong></td>
                <td><strong>{{ $allEnginesCount }}</strong></td>
                <td><strong>100%</strong></td>
            </tr>
            <tr>

            </tr>
        </tbody>
    </table>


    {{-- ++++++++++++++++++++++++ --}}

    <div class="title">
        <br>
        <h3 class="subtitle">1c. Catégories d'engins</h3>
    </div>

    <p>Cette partie traite des différents types de véhicules en fonction de leurs catégories .</p>
    <table>
        <thead>
            <tr>
                <th>CATEGORIE</th>
                <th>DISTANCE PARCOURUE</th>
                <th>NOMBRE</th>
                <th>POURC.</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($categoriesEngins as $key => $categorie)
                <tr>
                    <td>{{ $key }}</td>
                    <td> {{ $distancesCollection[$loop->iteration - 1] }} </td>
                    <td> {{ $categorie }} </td>
                    <td>{{ round(($categorie / $allEnginesCount) * 100, 2) }} %</td>
                </tr>
            @endforeach


            <tr class="highlight">
                <td><strong>TOTAL</strong></td>
                <td><strong>-</strong></td>
                <td><strong>{{ $allEnginesCount }}</strong></td>
                <td><strong>100%</strong></td>
            </tr>

        </tbody>
    </table>


</body>

</html>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Facture</title>
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
</body>

</html>
