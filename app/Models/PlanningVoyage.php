<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanningVoyage extends Model
{
    use HasFactory;

    protected $casts = [
        'order' => 'array',
    ];
}
