<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestataire extends Model
{
    use HasFactory;
    protected $table = 'fournisseur';
    public $timestamps = false;
    protected $primaryKey = 'code_fr';
    protected $connection ="oracle";


    public function reparations():HasMany
    {
        return $this->hasMany(Prestataire::Class);
    }
}
