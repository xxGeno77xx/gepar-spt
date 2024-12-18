<?php

use App\Models\User;
use App\Static\StoredProcedures;
use App\Support\Database\RolesEnum;
use Filament\Notifications\Notification;
use App\Http\Controllers\ReparationPdfController;
use App\Http\Controllers\OrdreMissionPdfController;
use Filament\Notifications\Actions\Action as NotificationActions;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/a', fn() => view('welcome'));

Route::get('ordre/N_{order}', [OrdreMissionPdfController::class, 'couleur'])->name('couleur');

Route::get('ordre/{order}', [OrdreMissionPdfController::class, 'blackWhite'])->name('pdfNoirBlanc');

Route::get('planning/{planning}', [OrdreMissionPdfController::class, 'planningVoyage'])->name('planningVoyage');

Route::get('ordre-de-route/{order}', [OrdreMissionPdfController::class, 'ordreDeRouteCouleur'])->name('ordreDeRouteCouleur');

Route::get('ordre-de-route_Bn/{order}', [OrdreMissionPdfController::class, 'ordreDeRouteBn'])->name('ordreDeRouteBn');

Route::get('reparationRecap/{reparation}', [ReparationPdfController::class, 'reparationRecap'])->name('reparationRecap');

Route::get('SitutationAnnuelle_{annee}', [OrdreMissionPdfController::class, 'dashboardEtat'])->name('dashboardEtat');

Route::get('/test', function () {

    $payload = '{
    "ClientContext": {
        "ClientReference": "c929f978-1be9-497a-8f30-c106faf61a2"
    },
    "SearchConfiguration": {
        "AssignResultTo": {
            "Division": "Toutes les divisions",
            "EmailNotification": "false",
            "RolesOrUsers": [
                "Administrator"
            ],
            "Type": "Role"
        },
        "PredefinedSearchName": "Filtrage de paiement",
        "WriteResultsToDatabase": "false",
        "ExcludeScreeningListMatches": "false",
        "DuplicateMatchSuppression": "false",
        "DuplicateMatchSuppressionSameDivisionOnly": "false"
    },
    "SearchInput": {
        "Records": [
            {
                "Entity": {
                    "EntityType": "Individual",
                    "Name": {
                        "First": "Donald",
                        "Last": "Trump"
                    },
                    "AdditionalInfo": [
                        {
                            "Date": {
                                "Day": 14,
                                "Month": 6,
                                "Year": 1946
                            },
                            "Type": "DOB"
                        }
                    ],
                    "Addresses": [
                        {
                            "Country": "USA",
                            "Type": "Current"
                        }
                    ]
                }
            }
        ]
    }
}';

    $endpoint = "192.168.60.43:8080/filtrage-api/filtrage/v1/search";

    $response = Http::withBody($payload, 'application/json')->post($endpoint);

    $clientReference = $response->collect()["ClientReference"];

    $apiRecords = $response->collect()["Records"];

    $recordDetails = $response->collect()["Records"];

    foreach ($apiRecords as $recordKey => $record) {

        dd($record["RecordDetails"]);
        dd($record["RecordDetails"]);
        Record::firstOrCreate([

            "client_ref" => $clientReference,
            "record" => $record["Record"],
            "resultid" => $record["ResultID"],
            "runid" => $record["RunID"],

        ]);

      $recordDetailId =  RecordDetail::firstOrCreate([
            "record_id"         => $record["Record"],
            "acceptlistid"      => $record["RecordDetails"]["AcceptListID"],
            "accountamount"     => $record["RecordDetails"]["AccountAmount"],
            "accountdate"       => $record["RecordDetails"]["AccountDate"],
            "accountgroupid"    => $record["RecordDetails"]["AccountGroupID"],
            "accountotherdata"  => $record["RecordDetails"]["AccountOtherData"],
            "accountproviderid" => $record["RecordDetails"]["AccountProviderID"],
            "accountmemberid"   => $record["RecordDetails"]["AccountMemberID"],
            "accounttype"       => $record["RecordDetails"]["AccountType"],
            "addedtoacceptlist" => $record["RecordDetails"]["AddedToAcceptList"],
            "predefinedsearch"  => $record["RecordDetails"]["PredefinedSearch"],
            "pdsversion"        => $record["RecordDetails"]["PDSVersion"],
            "dppa"              => $record["RecordDetails"]["DPPA"],
            "efttype"           => $record["RecordDetails"]["EFTType"],
            "entitytype"        => $record["RecordDetails"]["EntityType"],
            "gender"            => $record["RecordDetails"]["Gender"],
            "glb"               => $record["RecordDetails"]["GLB"],
            "lastupdateddate"   => $record["RecordDetails"]["LastUpdatedDate"],
            "searchdate"        => $record["RecordDetails"]["SearchDate"]

        ])->id;


        foreach($record["RecordDetails"]["AdditionalInfo"] as $additionalInfos)
        {
            AdditionalInfo::firstOrCreate([
                "record_detail_id" => $recordDetailId,
                "type" => $additionalInfos["Type"],
                "value" => $additionalInfos["Value"]
        ]);
        }

        

        foreach($record["RecordDetails"]["Addresses"] as $adress)
        {
            Adress::firstOrCreate([

                "record_detail_id"  => $recordDetailId,
                "country"           => $adress["country"],
                "type"              => $adress["value"]
            ]);
        }

        foreach($record["RecordDetails"]["Name"] as $name)
        {
            Name::firstOrCreate([

                "record_detail_id" => $recordDetailId,
                "first"         => $name["First"],
                "full"          => $name["Full"],
                "generation"    => $name["Generation"],
                "last"          => $name["Last"],
                "middle"        => $name["Middle"],
                "title"         => $name["Title"],
            ]);
        }

        $recordState = RecordState::firstOrCreate([
            "record_detail_id"  => $record["RecordDetails"]["RecordState"]["AddedToAcceptList"],
            "AddedToAcceptList" =>  $record["RecordDetails"]["RecordState"]["AddedToAcceptList"],
            "AlertState"        =>  $record["RecordDetails"]["RecordState"]["AlertState"],
            "AssignmentType"    =>  $record["RecordDetails"]["RecordState"]["AssignmentType"],
            "Status"            =>$record["RecordDetails"]["RecordState"]["Status"],
        ])->id;

        
        foreach($record["RecordDetails"]["RecordState"]["AssignmentType"]["MatchStates"] as $matchState){

            MatchState::firstOrCreate([
                "record_state_id"   =>  $recordState,
                "match_id"          =>  $matchState["MatchID"],
                "type"              =>  $matchState["Type"]
            ]);
        }
        // dd($record);

    }

    // dd($response->collect()["Records"]);

    $record = $response->collect()["Records"][0];

    $recordDetails = $response->collect()["Records"][0]["RecordDetails"];

    // dd($recordDetails);

    foreach ($recordDetails as $key => $detail) {
        dd($key, $detail);
        // RecordDetail::firstOrCreate([
        // "AcceptListID"                         => "AcceptListID"  ,
        // "AccountAmount"                        => "AccountAmount",
        // "AccountDate"                          =>  "AccountDate",
        // "AccountGroupID"                       => "AccountGroupID",
        // "AccountOtherData"                     =>  "AccountOtherData",
        // "AccountProviderID"                    => "AccountProviderID" ,
        // "AccountMemberID"                      => "AccountMemberID",
        // "AccountType"                          => "AccountType",
        // "AddedToAcceptList"                    => "AddedToAcceptList" ,
        // "AdditionalInfo"                       => "AdditionalInfo"    ,
        // "Addresses"                            => "Addresses"      ,
        // "PredefinedSearch"                     => "PredefinedSearch",
        // "PDSVersion"                           => "PDSVersion" ,
        // "DPPA"                                 => "DPPA"  ,
        // "EFTType"                              => "EFTType" ,
        // "EntityType"                           => "EntityType" ,
        // "Gender"                               => "Gender",
        // "GLB"                                  => ,
        // "LastUpdatedDate"                      => ,
        // "Name"                                 => ,
        // "RecordState"                          => ,
        // "SearchDate"                           => ,
        // ]);
    }

    return dd($response->collect()["ClientReference"][0]["RecordDetails"]);

    return view('welcome');

})->name('test');
