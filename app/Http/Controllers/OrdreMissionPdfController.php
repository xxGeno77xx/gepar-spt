<?php

namespace App\Http\Controllers;

use App\Models\OrdreDeMission;
use App\Models\PlanningVoyage;
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

    public function planningVoyage(PlanningVoyage $planning)
    {

        return Pdf::loadView('planningVoyage', ['planning' => $planning])
            ->stream('Ordre N '.$planning->id.'.pdf');
    }
}
