<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tvm extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    protected $casts = [
        'engine_id' => 'array',
        'engins_prix' => 'array',
    ];

    public function engines(): BelongsToMany
    {
        return $this->belongsToMany(Engine::class)->withPivot('montant');
    }
}
