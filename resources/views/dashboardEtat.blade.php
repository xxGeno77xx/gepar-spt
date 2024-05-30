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

    $allEnginesCount = Engine::where('state', '<>', StatesClass::Deactivated()->value)->count();

    $deuxRouesIDs = Type::whereIn('nom_type', [
        TypesClass::Transport_a_deux_roues()->value,
        TypesClass::Tricycle_motorises()->value,
    ])->pluck('id');

    $vehiculesAutomobiles = Engine::where('poids_a_vide', '<', 3.5)
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();

    $vehiculeUtilitairesLegers = Engine::whereBetween('poids_a_vide', [3.5, 12])
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();
        
    $vehiculeUtilitairesLourds = Engine::where('poids_a_vide', '>', 12)
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->whereNotIn('type_id', $deuxRouesIDs)
        ->get();

    $transportADeuxRoues = Engine::where(
        'type_id',
        Type::where('nom_type', TypesClass::Transport_a_deux_roues()->value)->first()->id,
    )
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->get();

    $tricyclesMotorises = Engine::where(
        'type_id',
        Type::where('nom_type', TypesClass::Tricycle_motorises()->value)->first()->id,
    )
        ->where('state', '<>', StatesClass::Deactivated()->value)
        ->get();


        // ============================== IDs======================

        $vehiculesAutomobilesIDs = $vehiculesAutomobiles->pluck("id")->toArray();
        $vehiculeUtilitairesLegersIDs = $vehiculeUtilitairesLegers->pluck("id")->toArray();
        $vehiculeUtilitairesLourdsIDs = $vehiculeUtilitairesLourds->pluck("id")->toArray();
        $transportADeuxRouesIDs = $transportADeuxRoues->pluck("id")->toArray();
        $tricyclesMotorisesIDs = $tricyclesMotorises->pluck("id")->toArray();

         // ============================== IDs======================


         //distances for engines in deffrent categories
         $vehiculesAutomobilesDistance = DistanceParcourue::whereIn("engine_id", $vehiculesAutomobilesIDs)->whereYear("date_distance_parcourue", now()->format("Y"))->sum("distance") ;

    $categoriesCollection = collect([
        $vehiculesAutomobiles,
        $vehiculeUtilitairesLourds,
        $vehiculeUtilitairesLegers,
        $transportADeuxRoues,
        $tricyclesMotorises,
    ]);

    $departementsWithEnginesIDs = Engine::whereNotNull('departement_id')
        ->distinct()
        ->pluck('departement_id')
        ->toArray();

    $enginesPerDepartment = Engine::whereNotNull('departement_id')
        ->selectRaw('COUNT(engines.id) as count')
        ->groupBy('departement_id')
        ->pluck('count')
        ->toArray();

    $typesCarburantWithEngineRecords = Engine::distinct()->pluck('carburant_id')->toArray();

    $enginesPerCarburant = Engine::whereIn('carburant_id', $typesCarburantWithEngineRecords)
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

    //================================================

@endphp

{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    Nombre d'engins du parc : {{ $allEnginesCount }}
    <br>
    Véhicules automobiles (4 roues dont le poids &lt; 3.5 t) : {{ $vehiculesAutomobiles->count() }} <br>
    Véhicules utilitaires léger (4 roues dont le poids &lt; 12 t) : {{ $vehiculeUtilitairesLegers->count() }}
    <br>
    Véhicules utilitaires lourds (4 roues dont le poids > 12 t) : {{ $vehiculeUtilitairesLourds->count() }}
    <br>
    Transports à 2 roues : {{ $transportADeuxRoues->count() }}
    <br>
    Tricycles motorisés : {{ $tricyclesMotorises->count() }}
    <hr>


    @php
        $vehiculesAutomobilesIDs = $vehiculesAutomobiles->pluck('id')->toArray();

        $consommationDeCarburant = [];

        foreach ($vehiculesAutomobilesIDs as $key => $engineID) {
            $consommationDeCarburant[] =
                ConsommationCarburant::where('engine_id', $engineID)->orderByDesc('id')->first()
                    ->kilometres_a_remplissage ?? Engine::find($engineID)->kilometrage_achat;
        }

        $distanceParcourueVehiculesAutomobiles = array_sum($consommationDeCarburant);

    @endphp

    VÉHICULES AUTOMOBILES
    <br>
    -Distance parcourure: {{ $distanceParcourueVehiculesAutomobiles }} Km
    <br>
    -Consommation de carburant:
    <br>
    -Carburants utilisés:

    <br>
    <hr>

    VÉHICULES UTILITAIRES LEGERS
    <br>
    -Distance parcourure:
    <br>
    -Consommation de carburant:
    <br>
    -Carburants utilisés:

    <br>
    <hr>

    VÉHICULES UTILITAIRES LOURDS
    <br>
    -Distance parcourure:
    <br>
    -Consommation de carburant:
    <br>
    -Carburants utilisés:

    <br>
    <hr>

    TRANSPORTS DEUX ROUES
    <br>
    -Distance parcourure:
    <br>
    -Consommation de carburant:
    <br>
    -Carburants utilisés:

    <br>
    <hr>

    TRICYCLES MOTORISES
    <br>
    -Distance parcourure:
    <br>
    -Consommation de carburant:
    <br>
    -Carburants utilisés:

    <br>
    <hr>



</body>

</html> --}}

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
                    <th>PART</th>
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
                    <th>PART</th>
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
                    <th>PART</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($categoriesEngins as $key => $categorie)
                    <tr>
                        <td>{{ $key }}</td>
                        {{-- <td><strong>{{ $categoriesCollection->offsetGet($loop->iteration - 1)->sum('distance_parcourue') }} --}}
                            <td><strong>   -                           </strong></td>
                        <td>{{ $categorie }}</td>
                        <td>{{ round(($categorie / $allEnginesCount) * 100, 2) }} %</td>
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


</body>

</html>
