<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteCharge extends Model
{
    use HasFactory;

    protected $table = 'mbudget.compte_budget';

    public $timestamps = false;

    protected $primaryKey = 'numero_compte';

    protected $connection = 'oracle';
}
