@php
use Carbon\Carbon;
    $additionnalData = DB::table('gepar.reparations')
        ->join('gepar.engines', 'gepar.engines.id', 'gepar.reparations.engine_id')
        ->join('mbudget.fournisseur', 'mbudget.fournisseur.code_fr', 'gepar.reparations.prestataire_id')
        ->where('gepar.reparations.id', $reparation->id)
        ->select(
            'reparations.id',
            'reparations.infos',
            'reparations.intitule_reparation',
            'reparations.main_oeuvre',
            'engines.plate_number',
            'fournisseur.raison_social_fr',
            'fournisseur.tel_fr',
            'fournisseur.adr_fr',
        )
        ->first();

    $achats = json_decode($additionnalData->infos);

@endphp


<html>

<head>
    <meta charset="utf-8">
    <title>Récapitulatif projet N° {{ $reparation->id }}</title>
    <link rel="stylesheet" href="style.css">
    <link rel="license" href="https://www.opensource.org/licenses/mit-license/">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@700&display=swap" rel="stylesheet">

</head>
<style>
    /* heading */


    .signature {
        text-align: right;
    }

    h1 {
        font: bold 100% sans-serif;
        letter-spacing: 0.5em;
        text-align: center;
        text-transform: uppercase;
    }

    /* table */

    table {
        font-size: 75%;
        table-layout: fixed;
        width: 100%;
    }

    th,
    td {
        border-width: 1px;
        padding: 0.5em;
        position: relative;
        text-align: center;
        border-radius: 0.25em;
        border-style: solid;
    }


    th {
        background: #EEE;
        border-color: #BBB;
    }

    td {
        border-color: #DDD;
    }

    /* page */

    html {
        font: 16px/1 'Open Sans', sans-serif;
    }

    body {
        height: auto;
        /* Suppression de la hauteur fixe */
        width: auto;
        padding: 0.5in;
        background: #fff;
        /* Mettre une couleur de fond pour la "page" */
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.5);
        /* Ajout d'une ombre pour l'effet de papier */
    }

    /* header, */
    article,
    aside {
        padding-right: 0.5in;
        width: 7.2in;
        /* Ajoutez du padding droit pour le contenu */
    }

    header h1 {
        /* background: #000;
        border-radius: 0.25em;
        color: #2628a3;
        /* margin: 150 0 0.5em; */
        margin-top: 1cm padding: 0.5em 0;
    }


    /* article */


    table.meta,
    table.inventory {
        margin: 0 0 3em;
    }



    /* table meta & balance */

    table.meta,
    table.balance {
        float: right;
        width: 36%;
    }

    /* table meta */

    table.meta th {
        width: 40%;
    }

    table.meta td {
        width: 60%;
    }

    /* table alpha */
    table.alpha th {
        width: 30%;
        font-weight: bold;
    }

    /* table items */

    table.inventory {
        clear: both;
        width: 100%;
    }

    table.inventory th {
        font-weight: bold;
        text-align: center;
    }

    table.inventory td:nth-child(1) {
        width: 26%;
    }

    table.inventory td:nth-child(2) {
        width: 38%;
    }

    table.inventory td:nth-child(3) {
        text-align: right;
        width: 12%;
    }

    table.inventory td:nth-child(4) {
        text-align: right;
        width: 12%;
    }

    table.inventory td:nth-child(5) {
        text-align: right;
        width: 12%;
    }

    /* table balance */

    table.balance th,
    table.balance td {
        width: 50%;
    }

    table.balance td {
        text-align: right;
    }

    /* aside */

    aside h1 {
        border: none;
        border-width: 0 0 1px;
        margin: 0 0 1em;
    }




    @media print {
        * {
            -webkit-print-color-adjust: exact;
        }

        html {
            background: none;
            padding: 0;
        }

        body {
            box-shadow: none;
            margin: 0;
        }

        span:empty {
            display: none;
        }

        .add,
        .cut {
            display: none;
        }
    }

    @page {
        margin: 0;
    }

    .cadre-arrondi {
        background-color: #f0f0f0;
        padding: 10px;
        border-radius: 15px;
        color: #333;
        width: 300px;
        margin: 20px auto;
        text-align: center;
        border: 1px solid #ccc;
        font-family: Arial, sans-serif;
    }

    .container {
        position: relative;
        width: 100%;
        height: 100%;
        /* background-image: url('assets/headerOnly.png'); */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;

    }

    /* Conteneur flex pour le texte LAPOSTE */
    .header-content {
        display: flex;
        align-items: center;
        /* Centrer verticalement le texte */
    }

    /* Style pour le texte "LAPOSTE" */
    .laposte-text {
        margin: 0;
        font-size: 24px;
        color: rgb(32, 32, 112);
        font-weight: bold;
        font-style: italic;
        font-family: 'Roboto Slab', serif;

    }

    /* Positionnement du logo dans le coin supérieur gauche */
    .logo {
        position: absolute;
        top: -50px;
        /* Ajuste la distance par rapport au haut de la page */
        left: -7px;
        /* Ajuste la distance par rapport au côté gauche */
        width: 120px;
        /* Taille du logo, vous pouvez ajuster selon vos besoins */
        height: auto;
        /* Maintenir les proportions du logo */

    }

    .undertext {
        font: bold 2px/2px;
        text-align: center;
        font-weight: bold;
        font-style: italic;
    }

    /* Style pour l'underline sous le texte */
    .divider {
        margin-top: 5%;
        display: block;
        width: 100%;
        height: 6px;
        background-color: #3121bd;
    }

    /* Si nécessaire, vous pouvez personnaliser l'espacement et les tailles selon vos besoins */


    .divider::before,
    .divider::after,
    .divider div {
        content: "";
        width: 25%;
        height: 100%;
        background-color: black;
    }

    .divider::before {
        width: 60%;
        /* Longue partie gauche */
    }

    .divider div {
        width: 10%;
        /* Petite section au milieu */
    }

    .divider::after {
        width: 25%;
        /* Petite section à droite */
    }
</style>

<body>
    <div class="container ">
        <header>
            <div class="header-content">
                <h1 class="laposte-text">
                    LA POSTE
                    <p class="undertext">SOCIETE DES POSTES DU TOGO</p>
                </h1>

                <hr class="divider">
            </div>
            <img src="assets/logo_poste.png" alt="Logo" class="logo">

        </header>


        <h1>DETAILS DES TRAVAUX DEMANDES</h1>

        <aside>

            <br>
            <br>
            <br>

            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Premier élément -->
                <div style="display: flex; align-items: center;">
                    <span>Nom de la société:</span>
                    <span class="cadre-arrondi" style="margin-left: 5px;">
                        {{ $additionnalData->raison_social_fr }}
                    </span>
                </div>
            </div>
            <br>
            <br>
            <div style="display: flex; align-items: center; gap: 20px; font-size: 0.65rem;">
                <!-- Premier élément -->
                <div style="display: flex; align-items: center;">
                    <span>BP:</span>
                    <span class="cadre-arrondi" style="margin-left: 5px; font-size: 0.65rem;">
                        {{ $additionnalData->bp ?? '-' }}
                    </span>
                    <span>Tél:</span>
                    <span class="cadre-arrondi" style="margin-left: 5px; font-size: 0.65rem;">
                        {{ $additionnalData->tel_fr }}
                    </span>
                </div>
            </div>
            <br>
            <br>
            <div style="display: flex; align-items: center; gap: 20px; font-size: 0.65rem;">
                <!-- Premier élément -->
                <div style="display: flex; align-items: center;">
                    <span>Adresse:</span>
                    <span class="cadre-arrondi" style="margin-left: 5px;">
                        {{ $additionnalData->adr_fr }}
                    </span>
                </div>
            </div>
            <br>
            <br>
            <br>
            <br>
            <article>
                <table class="alpha">
                    <tr>
                        <th><span>Intitulé du projet</span></th>
                        <td><span style="font-weight: bold;">{{ strtoupper($reparation->intitule_reparation) }}</span>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="inventory">
                    <thead>
                        <tr>
                            <th><span>Désignation</span></th>
                            <th><span>Prix</span></th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($achats as $key => $achat)
                            {

                            <tr>
                                <td> <span>{{ strtoupper($achat->data->Designation) }}</span></td>
                                <td><span>{{ number_format($achat->data->montant, 0, null, '.') }} FCFA</span></td>
                            </tr>

                            }
                        @endforeach

                        <tr>
                            <td> <span> MAIN D'OEUVRE</span></td>
                            <td><span>{{ number_format($reparation->main_oeuvre, 0, null, '.') }} FCFA</span></td>
                        </tr>

                    </tbody>
                </table>

                Main
                Référence du devis: {{ $reparation->ref_proforma }}
                <table class="balance">
                    <tr>
                        <th><span>Montant total</span></th>
                        <td><span></span><span>{{ number_format($reparation->cout_reparation, 0, null, '.') }}
                                FCFA</span>
                        </td>
                    </tr>
                </table>
            </article>
        </aside>
        <br>
        <br>
        <div>
            <div>
                <p><b>Vu par</b></p>
                <p><b>Le chef de Division</b></p>
                <p><b>Le Directeur de Département</b></p>

            </div>
            <br>

            <div class="signature">
                <p ><b>Le Directeur de Général</b></p>
                <p> Lomé, {{Carbon::parse($reparation->date_valid_dg)->format("d/m/Y")}}</p>
                <img src="assets/sign.png" alt=""  style="width: 12%; height: auto;">
            </div>

        </div>

    </div>

</body>

</html>
