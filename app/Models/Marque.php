<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marque extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    public function modeles()
    {
        return $this->hasMany(Modele::class);
    }

    public function engines()
    {
        return $this->hasManyThrough(Engine::class, Modele::class, 'marque_id', 'modele_id');

    }
}
