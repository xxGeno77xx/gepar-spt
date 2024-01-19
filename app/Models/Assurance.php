<?php

namespace App\Models;

use App\Support\Database\StatesClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Engine;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assurance extends Model
{
    use HasFactory;
    use SoftDeletes;
    public function engine ()
    {
        return $this->belongsTo(Engine::Class);
    }

    
   
}
