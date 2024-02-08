<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carburant extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    public function modele()
    {
        return $this->hasMany(Engine::class);
    }
}
