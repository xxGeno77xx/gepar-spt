<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visite extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function engine()
    {
        return $this->belongsTo(Engine::class);
    }
}
