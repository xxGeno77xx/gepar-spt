<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    use HasFactory;

    public const UN_MOIS = 30;

    public const DEUX_SEMAINES = 14;

    public const UNE_SEMAINE = 7;

    public const UN_MOIS_VALUE = 'Rappels à 1 mois';

    public const DEUX_SEMAINES_VALUE = 'Rappels à 2 semaines';

    public const UNE_SEMAINE_VALUE = 'Rappels à 1 semaine';
}
