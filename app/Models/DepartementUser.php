<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartementUser extends Model
{
    use HasFactory;

    protected $table = 'departement_user';

    public $timestamps = false;

    // protected $primaryKey = 'code_centre';

    protected $connection = 'oracle';
}
