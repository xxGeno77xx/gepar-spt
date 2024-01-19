<?php

namespace App\Models;

use App\Models\Engine;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visite extends Model
{
    use HasFactory;
    use SoftDeletes;
    public function engine ()
    {
        return $this->belongsTo(Engine::Class);
    }
}
