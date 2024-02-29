<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdreDeMission extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    protected $casts = [
        'agents' => 'array',
        'lieu' => 'array',
    ];
}
