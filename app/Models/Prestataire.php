<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestataire extends Model
{
    use HasFactory;

    public function reparations():HasMany
    {
        return $this->hasMany(Prestataire::Class);
    }
}
