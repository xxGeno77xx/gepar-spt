@php
   use Carbon\Carbon;
@endphp
<x-mail::message>
Bonjour,
Nous tenons à vous informer que la TVM de certains de vos engins approchent de leur date d'expiration. 
Il est important de prendre les mesures nécessaires pour garantir la sécurité et la conformité de vos engins. 
Voici les détails de vos engins:
@foreach ($mailableEngines as $engine) <br>   
Engin N°{{$loop->iteration}} <br> 
Numéro de plaque: {{$engine->plate_number}} <br> 
Marque: {{$engine->nom_marque}} <br> 
Visite technique: du {{Carbon::parse($engine->date_initiale)->format('d-m-Y')}} au {{Carbon::parse($engine->date_expiration)->format('d-m-Y')}}


@php
    $url= route("filament.resources.engines.view",["record" => $engine->id]);
@endphp

<x-mail::button :url="$url">
Voir l'engin N° {{$loop->iteration}}    
</x-mail::button>

@endforeach
Merci, {{ config('app.name') }}
La Société des Postes du Togo.
</x-mail::message>
