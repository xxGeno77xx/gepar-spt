<?php

use App\Models\User;
use App\Support\Database\RolesEnum;
use App\Http\Controllers\OrdreMissionPdfController;

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

Route::get('/a', fn () => view('welcome'));

Route::get('ordre/N_{order}', [OrdreMissionPdfController::class, 'couleur'])->name('couleur');

Route::get('ordre/{order}', [OrdreMissionPdfController::class, 'blackWhite'])->name('pdfNoirBlanc');

Route::get('planning/{planning}', [OrdreMissionPdfController::class, 'planningVoyage'])->name('planningVoyage');

Route::get('ordre-de-route/{order}', [OrdreMissionPdfController::class, 'ordreDeRouteCouleur'])->name('ordreDeRouteCouleur');

Route::get('ordre-de-route_Bn/{order}', [OrdreMissionPdfController::class, 'ordreDeRouteBn'])->name('ordreDeRouteBn');

Route::get('SitutationAnnuelle_{annee}', [OrdreMissionPdfController::class, 'dashboardEtat'])->name('dashboardEtat');

Route::get('/test/{id}', function($id){

    return dd($id);

})->name('test');
