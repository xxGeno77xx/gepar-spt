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

    // $solde = StoredProcedures::soldecourant(9240001197001000);


    $realDestination = User::join("departement_user", "departement_user.user_id", "users.id")->where("departement_user.departement_code_centre", 30)->role(RolesEnum::Directeur()->value)->pluck("users.id");

    $users = User::whereIn("id", $realDestination)->get();
    // $u = User::role(RolesEnum::Directeur()->value)->get();

    // dd($realDestination);

    Notification::make()
        ->title('Demande de validation')
        ->body(' en attente de validation')
        ->actions([
            NotificationActions::make('voir')
                // ->url(route('filament.resources.reparations.view', $this->record->id), shouldOpenInNewTab: true)
                ->button()
                ->color('primary'),

        ])
        ->sendToDatabase($users);

    // return view('budget');

})->name('test');
