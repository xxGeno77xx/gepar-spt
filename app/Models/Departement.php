<?php

namespace App\Models;

use App\Models\Users;
use App\Models\Engine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'CENTRE';
    public $timestamps = false;
    protected $primaryKey = 'code_centre';
    protected $connection ="oracle";
    

    public function engins()
    {
        return $this->hasMany(Engine::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    
    }

    public function EngineChauffeur():HasOneThrough
    {
        return $this->hasOneThrough(Engine::class, Chauffeur::class);
    }
}
