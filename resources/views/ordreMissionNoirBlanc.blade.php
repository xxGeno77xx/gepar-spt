
@php
    use App\Models\Chauffeur;
    $chauffeur = Chauffeur::find($order->chauffeur_id)->value("name");
 
  $agents = $order->agents;

@endphp



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<style>
body, h1, h2, h3, h4, h5, h6  {
  font-family: "Bookman Old Style";
}

        .container {
            display: flex;
            justify-content: center;
        }
        .centered-div {
            text-align: center;
            background-color: #f0f0f0; /* Exemple de couleur de fond */
          
        }
    </style>
<body>

    <p class="left-hand-heading">
        NA/KAMK 
    </p>

    <span style="font-weight: bold; font-style: italic;">
        <u>N° qsssssssssss</u> SPT/DG/DRHP/DPAS
    </span>

    <div class="container">
        <div class="centered-div">
            <h1>ORDRE DE MISSION</h1>
        </div>
    </div>
 
    <p>Il est ordonné à Messieurs:</p>

<ul>
    @foreach ($agents as $key => $agent )

        
        <p><li>-{{$agent["Nom"]}}, {{$agent["Désignation"]}}</li></p> 
        
       
    @endforeach
</ul>

</body>
</html>

