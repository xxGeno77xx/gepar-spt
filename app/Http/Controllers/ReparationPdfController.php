<?php

namespace App\Http\Controllers;

use App\Models\Reparation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReparationPdfController extends Controller
{
    public function reparationRecap(Reparation $reparation)
    {
        return Pdf::loadView('reparationRecap', ['reparation' => $reparation])
            ->stream('Réparation N°'.$reparation->id.'.pdf');
    }

}
