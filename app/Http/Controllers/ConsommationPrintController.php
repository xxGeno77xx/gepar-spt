<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsommationPrintController extends Controller
{
    public function consommationForDept($data, $startDate, $endDate)
    {
        return Pdf::loadView('consommationForDept', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            ])
            ->stream('consommation_'.$startDate. '_'.$startDate.'.pdf');
    }
}
