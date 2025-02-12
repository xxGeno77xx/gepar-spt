@php
use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récap Consommation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            position: relative;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
            margin-top: -10px;
        }

        th,
        td {
            border: 1px solid black;
            text-align: center;
            padding: 3px;
        }

        th {
            background-color: #f2f2f2;
        }

        tfoot td {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        /* Positionnement absolu des mentions dans le coin supérieur droit */
        .header-info {
            position: absolute;
            top: 1px;
            right: 10px;
            text-align: left;
            font-size: 12px;
        }

        .header-info p {
            margin: -5;
            padding: 5px 0;
        }

        /* Positionnement du logo */
        .logo {
            margin-top: 1px;
            margin-bottom: -120px;
            display: block;
            width: auto;
            height: 190;
        }

        /* Positionnement absolu des mentions dans les coins */
        .timbre-bureau {
            position: absolute;
            top: 915px;
            left: 0;
            text-align: left;
            font-size: 12px;
            margin: 0;
        }

        .signature-agent {
            position: absolute;
            bottom: 0;
            left: 0;
            text-align: left;
            font-size: 12px;
            margin: 0;
        }

        .timbre-transporteur {
            position: absolute;
            top: 915px;
            right: 0;
            text-align: right;
            font-size: 12px;
            margin: 0;
        }

        .signature-transporteur {
            position: absolute;
            bottom: 0;
            right: 0;
            text-align: right;
            font-size: 12px;
            margin: 0;
        }

        .title {
            margin-top: -30px;
        }
    </style>
</head>

<body>

    <!-- Logo avec moins d'espace en haut -->
    <div class="logo">
        <img src="assets/logo_poste.png" alt="">
    </div>

    <!-- Section pour les mentions supplémentaires au-dessus du tableau -->
    <div class="header-info">
        <p><strong>Date début:</strong> {{Carbon::parse($startDate)->format('d M Y')}}</p>
        <p><strong>Date fin:</strong> {{Carbon::parse($endDate)->format('d M Y')}}</p>
        <p><strong>Lomé, le :</strong> {{ date('d M Y') }}</p>
    </div>

    <div class="title">
        <h4> <strong>RÉCAPITULATIF DES DOTATIONS DE CARBURANTS</strong></h4>
    </div>

    <table>
        <thead>
            <tr>
                <th>Immatriculation</th>
                <th>Centre</th>
                <th>Type de carburant</th>
                <th>Consommation (L)</th>
                <th>Nombre de prises</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody class="divide-gray-100 border-t border-gray-100">
            @foreach ($data as $key => $engine)
                <tr class="hover:bg-gray-50 border-b">
                    <th class="px-6 py-4 font-normal text-gray-900">{{ $engine->plate_number}}</th>
                    <th class="px-6 py-4 font-normal text-gray-900">{{ $engine->sigle_centre}}</th>
                    <th class="px-6 py-4 font-normal text-gray-900">{{ $engine->type_carburant }}</th>
                    <th class="px-6 py-4 font-normal text-gray-900">{{ number_format($engine->consommation, 0 , null, ' ') }}</th>
                    <th class="px-6 py-4 font-normal text-gray-900">{{ number_format($engine->nombreprises, 0 , null, ' ') }}</th>
                    <th class="px-6 py-4 font-normal text-gray-900">{{ number_format($engine->montant, 0 , null, ' ') }}</th>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTAUX</td>
                <td>{{number_format($totalConsommation, 0 , null, ' ') }}</td>
                <td>{{number_format($nombreTotalPrises, 0 , null, ' ') }}</td>
                <td>{{number_format($totalMontant, 0 , null, ' ') }}</td>
            </tr>
        </tfoot>
    </table>



</body>

</html>
