<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Modele;

class Marque extends Model
{
    use HasFactory;

    public function modeles ()
    {
        return $this->hasMany(Modele::Class);
    }


    public function engines()
    {
        return $this->hasManyThrough( Engine::Class, Modele::class, 'marque_id', 'modele_id');

    }
}
