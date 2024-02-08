<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Type extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    protected $table = 'types_engins';

    public function engines()
    {
        return $this->hasMany(Engine::class);
    }

    public function modele(): HasOne
    {
        return $this->hasOne(Modele::class);
    }
}
