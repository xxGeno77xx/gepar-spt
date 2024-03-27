@php
    use App\Models\Chauffeur;
    use App\Models\Engine;
    use Carbon\Carbon;

    $chauffeur = Chauffeur::find($order->chauffeur_id)->value('fullname');
    $moyenTransport = Engine::find($order->engine_id)->value('plate_number');
    $agents = $order->agents;

@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre N° {{ $order->numero_ordre }}</title>
    {{-- <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"> --}}

    <style>
        @font-face {
            font-family: 'Bookman Old Style';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url("fonts/BKMNOS.ttf") format('truetype');
        }

        body {
            font-family: 'Bookman Old Style', sans-serif;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            /* font-family: "Bookman Old Style"; */
            font-size: 17px;

        }

        .container {
            position: relative;
            width: 100%;
            height: 100%;
            /* background-image: url('assets/papier.png'); */
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

        .async {
            display: flex;
        }
    </style>

</head>

<body>
    <div class="container ">
        <div class="top-content">
            <div class="main">
                <p class="left-hand-heading">
                    NA/KAMK
                </p>
                <span style="font-weight: bold; font-style: italic;">
                    N° <u> 000{{ $order->numero_ordre }}</u> /SPT/DG/DRHP/DPAS
                </span>
                <div class="centered-div">
                    <h1 style="font-weight: bold; font-family: Bookman Old Style;"><u>ORDRE DE MISSION</u></h1>
                </div>
                <p style="font-family: Bookman Old Style;">Il est ordonné à Messieurs:</p>
                <ul style="list-style-type:none;">
                    @foreach ($agents as $key => $agent)
                        <li style="margin-bottom: 15px;">-{{ $agent['Nom'] }}, {{ $agent['Désignation'] }}</li>
                    @endforeach

                </ul>

                <p>de se rendre en mission à l'intérieur du pays.</p>

                <br>
                <div>
                    <div style="display: inline-block; width: 34%;   vertical-align: top;">
                        <u style="font-weight: bold; margin-right: 65px; ">Objet de la mission</u>:
                    </div>
                    <div style="display: inline-block; width: 60%; vertical-align: top;">
                        {{ $order->objet_mission }}
                    </div>
                </div>

                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 104px">Date de départ</u>:
                        {{ Carbon::parse($order->date_de_depart)->translatedFormat('d F Y') }}
                    </span>
                </p>
                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 107px ">Date de retour</u>:
                        {{ Carbon::parse($order->date_de_retour)->translatedFormat('d F Y') }}
                    </span>
                </p>
                <p>
                    <span>
                        <u style="font-weight: bold; font-style:italic; margin-right: 60px ">Moyen de transport </u>:
                        Véhicule {{ $moyenTransport }}
                    </span>
                </p>
            </div>

            <div class="bottom-right">
                <p style="marhin-right 80cm">Lomé, {{ today()->translatedFormat('d F Y') }}</p>
                <p>Le Directeur Général de la <br>Société des Postes du Togo</p>
                <br>
                <br>
                <p style="font-weight: bold;"><u>Kwadzo Dzodzro KWASI</u></p>
            </div>
        </div>
    </div>



</body>

</html>
