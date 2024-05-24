<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliceAssurance extends Model
{
    use HasFactory;

    protected $connection = 'oracle';

    protected $table = 'polices_assurances';

    public $timestamps = 'polices_assurances';
}
