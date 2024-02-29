<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Division extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    public function engins()
    {
        return $this->hasMany(Engine::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);

    }

    public function EngineChauffeur(): HasOneThrough
    {
        return $this->hasOneThrough(Engine::class, Chauffeur::class);
    }
}
