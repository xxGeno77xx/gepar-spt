<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modele extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    public function marque()
    {
        return $this->belongsTo(Marque::class, 'marque_id');
    }

    public function engines()
    {
        return $this->hasMany(Engine::class, 'modele_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
