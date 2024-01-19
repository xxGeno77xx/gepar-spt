<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Engine;
class Carburant extends Model
{
    use HasFactory;

    public function modele ()
    {
        return $this->hasMany(Engine::Class);
    }

 
}
