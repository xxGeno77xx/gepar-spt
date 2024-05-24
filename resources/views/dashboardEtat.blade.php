@php

    use App\Models\Engine;
    use App\Models\Type;
     use App\Models\ConsommationCarburant;
    use App\Support\Database\TypesClass;

    $allEnginesCount = Engine::count();

    $vehiculesAutomobiles = Engine::where('poids_a_vide',"<", 3.5)
        ->whereNot('type_id', Type::where("nom_type", TypesClass::Transport_a_deux_roues()->value)->first()->id)
        ->where('type_id', Type::where("nom_type", TypesClass::Tricycle_motorises()->value)->first()->id)
        ->get();

    $vehiculeUtilitairesLegers = Engine::whereBetween('poids_a_vide',[3.5, 12])
        ->whereNot('type_id', Type::where("nom_type", TypesClass::Transport_a_deux_roues()->value)->first()->id)
        ->where('type_id', Type::where("nom_type", TypesClass::Tricycle_motorises()->value)->first()->id)
        ->get();;

    $vehiculeUtilitairesLourds = Engine::where('poids_a_vide',">", 12)
        ->whereNot('type_id', Type::where("nom_type", TypesClass::Transport_a_deux_roues()->value)->first()->id)
        ->where('type_id', Type::where("nom_type", TypesClass::Tricycle_motorises()->value)->first()->id)
        ->get();

    $transportADeuxRoues = Engine::where('type_id', Type::where("nom_type", TypesClass::Transport_a_deux_roues()->value)->first()->id)->get();

    $tricyclesMotorises = Engine::where('type_id', Type::where("nom_type", TypesClass::Tricycle_motorises()->value)->first()->id)->get();

    $categoriesCollection = collect([
        $vehiculesAutomobiles,
        $vehiculeUtilitairesLourds,
        $vehiculeUtilitairesLegers,
        $transportADeuxRoues,
        $tricyclesMotorises
]);

    // dd($categoriesCollection);

    // ===========================================
    // $FraisDeReparation = Reparation::where('engine_id', $this->record->id)->sum('cout_reparation');

// $Kilometrage = ConsommationCarburant::where('engine_id', $this->record->id)
//     ->orderByDesc('id')
//     ->first()
//     ->kilometres_a_remplissage ?? $this->record->kilometrage_achat;

// $nombreDeReparations = Reparation::where('engine_id', $this->record->id)?->count();
//================================================


@endphp

<!DOCTYPE html>
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
    Nombre de véhicules automobiles (4 roues dont le poids &lt; 3.5 t) : {{ $vehiculesAutomobiles->count()}} <br>
        Nombre de véhicules utilitaires léger (4 roues dont le poids &lt; 12 t) : {{ $vehiculeUtilitairesLegers->count()}} <br>
            Nombre de véhicules utilitaires lourds (4 roues dont le poids > 12 t) : {{ $vehiculeUtilitairesLourds->count() }}
            <br>
            Transports à 2 roues : {{ $transportADeuxRoues->count() }}
            <br>
            Tricycles motorisés : {{ $tricyclesMotorises->count() }}
            <hr>


            @php
                $vehiculesAutomobilesIDs = $vehiculesAutomobiles->pluck("id")->toArray();
 
                $consommationDeCarburant = [];

                foreach ($vehiculesAutomobilesIDs as $key => $engineID) {
                    $consommationDeCarburant[] = ConsommationCarburant::where("engine_id",$engineID)
                    ->orderByDesc('id')
                    ->first()
                    ->kilometres_a_remplissage ?? $this->record->kilometrage_achat;
                }
              
                $distanceParcourueVehiculesAutomobiles = (array_sum($consommationDeCarburant ));
                                                                
            @endphp

            VÉHICULES AUTOMOBILES
            <br>
            -Distance parcourure: {{$distanceParcourueVehiculesAutomobiles}} Km
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

            {{-- @dd($engineCollection->pluck("id")) --}}
                
           

</body>

</html>
