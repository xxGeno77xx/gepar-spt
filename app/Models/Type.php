<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Type extends Model
{
    use HasFactory;

    public function engines ()
    {
        return $this->hasMany(Engine::Class);
    }

    public function modele ():HasOne
    {
        return $this->hasOne(Modele::Class);
    }
}
