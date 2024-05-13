<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Engine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'oracle';

    public function reparations()
    {
        return $this->hasMany(Reparation::class);
    }

    public function modele()
    {
        return $this->belongsTo(Modele::class, 'modele_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function carburant()
    {
        return $this->belongsTo(Carburant::class);
    }

    public function assurances()
    {
        return $this->hasMany(Assurance::class);
    }   //return all insurances at once

    public function assurance(): HasOne
    {
        return $this->hasOne(Assurance::class)->latestOfMany();
        // return latest entry in insurance table for a given engine....
        //currently used in the engines table on filament admin pannel
        //to retrieve latest insurance
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'chauffeur_id');
    }

    // public function departement():BelongsTo
    // {
    //     return $this->belongsTo(Departement::Class);
    // }

    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    // public function tvms()
    // {
    //     return $this->hasMany(Tvm::class);
    // }

    public function tvms(): BelongsToMany
    {
        return $this->belongsToMany(Tvm::class)->withPivot("montant");
    }

    public function visite()
    {
        return $this->hasOne(Visite::class)->latestOfMany();
    }

    public function consommationCarburants(): HasMany
    {
        return $this->hasMany(ConsommationCarburant::class);
    }

    // public function chauffeur(): HasOne
    // {
    //     return $this->hasOne(Chauffeur::class);
    // }

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }

    public function ordreDeMissions(): HasMany
    {
        return $this->hasMany(OrdreDeMission::class);
    }
}
