<?php

namespace App\Models;

use App\Models\Type;
use App\Models\Marque;
use App\Models\Modele;
use App\Models\Assurance;
use App\Models\Carburant;
use App\Models\Reparation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Filament\Resources\EngineResource\RelationManagers\AssurancesRelationManager;

class Engine extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function reparations()
    {
        return $this->hasMany(Reparation::Class);
    }

    public function modele()
    {
        return $this->belongsTo(Modele::Class,'modele_id');
    }

    
    public function type()
    {
        return $this->belongsTo(Type::Class);
    }


    public function carburant()
    {
        return $this->belongsTo(Carburant::Class);
    }

    public function assurances()
    {
        return $this->hasMany(Assurance::Class);   
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
        return $this->belongsTo(Departement::Class, 'chauffeur_id');
    }

    // public function departement():BelongsTo
    // {
    //     return $this->belongsTo(Departement::Class);
    // }

    public function visites()
    {
        return $this->hasMany(Visite::Class);
    }
    
    public function visite()
    {
        return $this->hasOne(Visite::Class)->latestOfMany();
    }

    public function consommationCarburants():HasMany
    {
        return $this->hasMany(ConsommationCarburant::class);
    }

    public function chauffeur():HasOne
    {
        return $this->hasOne(Chauffeur::class);
    }


}
