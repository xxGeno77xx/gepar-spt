@php
   use Carbon\Carbon;
@endphp

<x-mail::message>
Bonjour,
Nous tenons à vous informer que les assurances de vos engins approchent de leurs dates d'expiration. 
Il est important de prendre les mesures nécessaires pour garantir la sécurité et la conformité de vos engins. 
Voici les détails de vos engins:

@foreach ($mailableEngines as $engine)   
Engin N°{{$loop->iteration}}<br>
Numéro de plaque: {{$engine->plate_number}}<br>
{{-- Marque: {{$engine->nom_marque}} ({{$engine->nom_modele}}) --}}
<br>
Assurance: du {{Carbon::parse($engine->date_debut)->format('d-m-Y')}} au {{Carbon::parse($engine->date_fin)->format('d-m-Y')}}

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
