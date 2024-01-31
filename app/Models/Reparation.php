<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reparation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'infos' => 'array',
    ];

    public function engine()
    {
        return $this->belongsTo(Engine::class);
    }

    public function typeReparations(): BelongsToMany
    {
        return $this->belongstoMany(TypeReparation::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsto(Prestataire::class);
    }
}
