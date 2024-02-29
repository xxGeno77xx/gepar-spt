<?php

namespace App\Http\Controllers;

use App\Models\OrdreDeMission;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdreMissionPdfController extends Controller
{
    public function couleur(OrdreDeMission $order)
    {

        return Pdf::loadView('ordreMission', ['order' => $order])
            ->stream('Ordre N '.$order->numero_ordre.'.pdf');
    }

    public function blackWhite(OrdreDeMission $order)
    {

        return Pdf::loadView('ordreMissionNoirBlanc', ['order' => $order])
            ->stream('Ordre N '.$order->numero_ordre.'.pdf');
    }
}
