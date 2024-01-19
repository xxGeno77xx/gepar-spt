<?php

namespace App\Models;

use App\Models\Engine;
use App\Models\Marque;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Modele extends Model
{
    use HasFactory;

    public function marque ()
    {
        return $this->belongsTo(Marque::Class,'marque_id');
    }

    public function engines ()
    {
        return $this->hasMany(Engine::Class,'modele_id');
    }

    public function type ():BelongsTo
    {
        return $this->belongsTo(Type::Class);
    }
}
