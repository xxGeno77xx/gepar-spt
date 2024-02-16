<div>
</head>
<header style="background-color: #60D0E4">
    <div class="headerSection">
        <!-- As a logo we take an SVG element and add the name in an standard H1 element behind it. -->
        <div class="logoAndName">
            <img src="{{ asset('assets/logo_poste.png') }}" alt="">
            <h1>Société des postes du Togo</h1>
        </div>
        <!-- Details about the invoice are on the right top side of each page. -->
        <div class="invoiceDetails">
            <h2></h2>
            <p>
                {{ now()->translatedFormat('l, d-m-Y H:i:s') }}
            </p>
        </div>
    </div>
    <h3 style="text-align: center">Relevé des consommations de carburant</h3>
    <!-- The two header rows are divided by an blue line, we use the HR element for this. -->
    <hr />
    <div class="headerSection">
        <!-- The clients details come on the left side below the logo and company name. -->
        <div>
            <h3> Matricule : <span style="font-weight: bold; color:black">{{ $plate_number }}</span></h3>
            <p>
                <b>Marque : <span style="font-weight: bold; color:black">{{ $marque }}</span></b>

                <br />

                <b>Modèle : <span style="font-weight: bold; color:black">{{ $modele }}</span></b>

                <br />

                <b>Carburant : <span style="font-weight: bold; color:black">{{ $carburant }}</span></b>

                <br />

                <b>Type : <span style="font-weight: bold; color:black">{{ $type }}</span></b>

                <br />

            </p>
        </div>
        <!-- Additional details can be placed below the invoice details. -->
        <div>
            <h3>Période</h3>
            <p>
                <b>Du {{$debutPeriode}} au {{$finPeriode}}</b>
            </p>
            <h3>Service utilisateur</h3>
            <p>
                <b>{{$departement}}</b>
            </p>
        </div>
    </div>
</header>
<br>
<br>

<body>
    <main>
        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>
                            {{ $column->getLabel() }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td>
                            {{ $row[$column->getName()] }}
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="summary">
            <tr class="total">
                <th>
                    Total
                </th>
                <td style="border: none">
                    {{ $total }} Litres
                </td>
            </tr>
        </table>
        <b>Consommation moyenne pour la période: {{$consoMoyenne}} litres.</b>
    </main>
    <hr />
    <div>
        <div>
            <h2><b>Vu par la hiérarchie</b></h2>
            <p><b>Nom et prénom:</b></p>
            <p><b>Signature </b></p>
        </div>
    </div>
</body>
<footer>
    <p><b>NB: Le présent état doit être transmis à la division des Affaires Générales à la fin de chaque mois</b></p>
</footer>
</div>