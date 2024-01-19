<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chauffeur extends Model
{
    use HasFactory;

    public function engine():BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }
}
