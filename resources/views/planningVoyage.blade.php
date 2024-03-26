@php
    use App\Models\Chauffeur;
    use App\Models\Engine;
    use App\Models\Departement;
    use Carbon\Carbon;
    use App\Models\Pays;

    $row = $planning->order;

    foreach ($row as $coll) {
        $data[] = collect($coll);
    }

    if ($planning->exterieur == 1) {
        $destination = 'extérieur';
    } else {
        $destination = 'intérieur';
    }

// @dd($planning)
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning de voyage N° {{$planning->id}}</title>
    <style>
        body,h2 {
            font-family: 'Bookman Old Style', sans-serif;
            font-size: 13.3px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2e2e2e;
            color:#ddd
        }
        @font-face {
            font-family: 'Bookman Old Style';
            font-weight: normal;
            font-style: normal;
            font-variant: normal;
            src: url("fonts/BKMNOS.ttf") format('truetype');
        }

    </style>
</head>

<body>


    {{-- <div style="text-align: center; margin-bottom: 20px; text-decoration:underline; font-family: 'Bookman Old Style', sans-serif;"> --}}
        <h2 style="text-align: center;font-size:20px; margin-bottom: 20px; text-decoration:underline; font-family: 'Bookman Old Style', sans-serif;">Ordre de mission pour les chauffeurs à l'{{ $destination }} du pays</h2>
    {{-- </div> --}}

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>NOMS ET PRENOMS</th>
                    <th>AFFECTATION</th>
                    <th>DATES</th>
                    @if ($planning->exterieur == 1)
                        <th>DESTINATION</th>
                    @endif
                </tr>
            </thead>
            <tbody>

                @foreach ($data as $row)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ Chauffeur::find($row['chauffeur'])->fullname }}</td>
                        <td>{{ Departement::find($row['affectation'])->sigle_centre }}</td>
                        <td>{{ Carbon::parse($row['date_debut'])->translatedFormat('d/m') }} au
                            {{ Carbon::parse($row['date_fin'])->translatedFormat('d/m/Y') }}</td>
                        @if ($planning->exterieur == 1)
                            <td>{{ Pays::find($row['pays'])->libelle }}</td>
                        @endif
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</body>

</html>
