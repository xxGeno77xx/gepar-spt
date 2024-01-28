@php
    use Illuminate\Support\Facades\DB;
    $content = DB::connection('oracle')->table('fournisseur')->where('code_fr', $getRecord()->prestataire_id)->first();
@endphp

<div class="pl-4">
    {{ $content->raison_social_fr}}
</div>
