<?php

namespace App\Models;

use App\Models\AffectationChauffeur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chauffeur extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    public function ordreDeMissions(): HasMany
    {
        return $this->hasMany(OrdreDeMission::class);
    }

    public function affectationChauffeurs(): HasMany
    {
        return $this->hasMany(AffectationChauffeur::class);
    }

    
    public function categoriePermis():BelongsToMany
    {
        return $this->belongsToMany(Permis::class);
    }
}
