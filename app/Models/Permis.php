<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Database\CategoryPermisClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permis extends Model
{
    use HasFactory;

    protected $connection ="oracle";
    protected $table = "categories_permis";

}
