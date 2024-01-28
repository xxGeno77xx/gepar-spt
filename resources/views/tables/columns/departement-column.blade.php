@php
    use Illuminate\Support\Facades\DB;
    $content = DB::connection('oracle')->table('centre')->where('code_centre', $getRecord()->departement_id)->first();
@endphp

<div class="pl-4">
    {{  $content->sigle_centre}}
</div>
