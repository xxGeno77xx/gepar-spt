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

    public function ordreDeRouteCouleur(OrdreDeMission $order)
    {

        return Pdf::loadView('ordreDeRouteCouleur', ['order' => $order])
            ->stream('Ordre-de-route N° '.$order->id.'.pdf');
    }

    public function ordreDeRouteBn(OrdreDeMission $order)
    {

        return Pdf::loadView('ordreDeRouteBn', ['order' => $order])
            ->stream('Ordre-de-route N° '.$order->id.'.pdf');
    }

    public function dashboardEtat($annee)
    {
        return Pdf::loadView('dashboardEtat', ['annee' => $annee])
            ->stream('Situation annuelle_'.$annee.'.pdf');
    }
}
