<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectationChauffeur extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
}
