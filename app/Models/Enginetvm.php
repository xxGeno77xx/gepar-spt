<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enginetvm extends Model
{
    use HasFactory;

    protected $table = 'engine_tvm';
    protected $connection = 'oracle';
}
