<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypeReparation extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    public function reparations(): BelongsToMany
    {
        return $this->belongstoMany(Reparation::class);
    }
}
