@php
    use App\Models\Chauffeur;
    use App\Models\Engine;
    use Carbon\Carbon;

    $chauffeur = Chauffeur::find($order->chauffeur_id)->value('name');
    $moyenTransport = Engine::find($order->engine_id)->value('plate_number');
    $agents = $order->agents;

@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre N° {{$order->numero_ordre}}</title>
    {{-- <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> --}}
    <style>


        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: "Bookman Old Style";
        }

        .container {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url('assets/papier.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

        }

        .top-content {
            padding-top: 4cm;
        }

        .centered-div {
            width: 50%;
            /* Changer la largeur selon vos besoins */
            margin: 0 auto;
            /* La marge automatique sur les côtés horizontaux centre l'élément */
            /* padding-left: 2.2cm; */
            /* Exemple de remplissage intérieur */
        }

        .main {
            padding-left: 1.8cm;
            margin-right: 1cm;
        }

        .bottom-right {
            text-align: right;
            padding-right: 4cm;
        }
        
    </style>

</head>

<body>
    <div class="container ">
        <div class="top-content" >
            <div class="main">
                <p class="left-hand-heading" >
                    NA/KAMK
                </p>
                <span style="font-weight: bold; font-style: italic;">
                    N° <u> {{ $order->numero_ordre }}</u> /SPT/DG/DRHP/DPAS
                </span>
                <div class="centered-div" >
                    <h1 style="font-weight: bold; font-family: Bookman Old Style;"><u>ORDRE DE MISSION</u></h1>
                </div>
                <p  style="font-family: Bookman Old Style;">Il est ordonné à Messieurs:</p>
                <ul  style="list-style-type:none;">
                    @foreach ($agents as $key => $agent)
                        <p>
                            <li>-{{ $agent['Nom'] }}, {{ $agent['Désignation'] }} , en service à la Cellule Informatique;</li>
                        </p>
                    @endforeach

                </ul>

                <p>de se rendre en mission à l'intérieur du pays.</p>

                <br>
                {{-- <p> --}}
                    {{-- <div style="display: flex" >

                        <div><u style="font-weight: bold;  margin-right: 69px  ">Objet de la mission</u>:</div>

                        <div>{{ $order->objet_mission }} aux Bureaux de poste de aklakou et mandouri</div>
                    </div> --}}
                {{-- </p> --}}
               
                <div style="display: flex;">
                    <div class="box">Box 1</div>
                    <div class="box">Box 2</div>
                </div>
                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 104px">Date de départ</u>: 
                        {{ Carbon::parse($order->date_de_depart)->translatedFormat('d F Y') }}
                    </span>
                </p>
                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 105px ">Date de retour</u>:
                        {{ Carbon::parse($order->date_de_retour)->translatedFormat('d F Y') }}
                    </span>
                </p>
                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 67px ">Moyen de transport </u>:
                        Véhicule {{ $moyenTransport }}
                    </span>
                </p>
            </div>

            <div class="bottom-right">
                <p>Lomé, le 21 dec 2028</p>
                <p>Le Directeur Général de la</p>
                <p> Société des Postes du Togo</p>
                <br>
                <p style="font-weight: bold;"><u>Kwadzo Dzodzro KWASI</u></p>
            </div>
        </div>
    </div>
</body>

</html>
