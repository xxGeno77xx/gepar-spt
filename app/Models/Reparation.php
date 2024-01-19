<?php

namespace App\Models;

use App\Models\Engine;
use Illuminate\Database\Eloquent\Model;
use App\Support\Database\PermissionsClass;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reparation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
        'infos' => 'array',
    ];
    public function engine()
    {
        return $this->belongsTo(Engine::Class);
    }

    public function typeReparations():BelongsToMany
    {
        return $this->belongstoMany(TypeReparation::Class);
    }


    public function prestataire():BelongsTo
    {
        return $this->belongsto(Prestataire::Class);
    }





}
