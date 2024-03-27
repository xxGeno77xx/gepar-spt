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
    <title>Ordre-de-route N° {{ $order->numero_ordre }}</title>
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
            background-image: url('assets/papier.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

        }

        .top-content {
            padding-top: 4cm;
        }

        .centered-div {
            width: 75%;
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

        .flexed {
            display: flex
        }
    </style>

</head>

<body>
    <div class="container ">
        <div class="top-content">
            <div class="main">
                <p class="left-hand-heading">
                </p>
                <span style="font-weight: bold; font-style: italic;">
                </span>
                <div class="centered-div">
                    <h1 style="font-weight: bold; font-family: Bookman Old Style;"><u>ORDRE DE ROUTE N° </u></h1>
                </div>
                <br> 
                <br> 
                <br> 
                <p style="font-family: Bookman Old Style;">Il est ordonné à Messieurs
                    @foreach ($agents as $key => $agent)
                        {{ $agent['Nom'] }},
                    @endforeach
                    de se rendre de Lomé à XXXX pour {{ $order->objet_mission }}.
                </p>
                <br> 
                <br> 
                <br> 
                <div class="bottom-right">
                    <p style="marhin-right 80cm">A.............., {{Carbon::parse( $order->date_debut)->translatedFormat('d F Y') }}</p>
                </div>
                <br>
                <br>
                <br>
                <br> 
                <br> 
                <br> 
                <div style="overflow: auto;">
                    <div
                        style="width: 50%; float: left; padding: 20px; box-sizing: border-box;">
                        Vu au départ de ..............., le................................
                    </div>
                    <div
                        style="width: 50%; float: left; padding: 20px; box-sizing: border-box;">
                        Vu au départ de ..............., le................................
                    </div>
                </div>
                <br> 
                <br>&nbsp;
                <br> 
                <br> 
                <br> 
                <br> 
                <br> 
                <div style="overflow: auto;">
                    <div
                        style="width: 50%; float: left; padding: 20px; box-sizing: border-box;">
                        Vu à l'arrivée à ..............., le................................
                    </div>
                    <div
                        style="width: 50%; float: left; padding: 20px; box-sizing: border-box;">
                        Vu à l'arrivée à ..............., le................................
                    </div>
                </div>
            </div>
        </div>
</body>

</html>
