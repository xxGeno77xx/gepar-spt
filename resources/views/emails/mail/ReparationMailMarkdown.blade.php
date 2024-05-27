@php
use App\Models\Engine;
    $url= route("filament.resources.reparations.view",["record" => $record->id]);

    $engine = Engine::find($record->engine_id)->first();
@endphp

<x-mail::message>
Une demande de réparation pour l'engin immatriculé {{$engine->plate_number}} est en attendte de votre validation. Cliquez sur le bouton ci-dessous pour voir la demande:

<x-mail::button :url="$url">
Voir
</x-mail::button>

Merci, {{ config('app.name') }} <br>
La Société des Postes du Togo.

</x-mail::message>