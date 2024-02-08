<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsommationCarburant extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }
}
