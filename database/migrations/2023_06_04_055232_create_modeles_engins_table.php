<?php

use App\Support\Database\StatesClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     protected $connection = 'oracle';
     
    public function up(): void
    {

        Schema::dropIfExists('modeles');
        
        Schema::create('modeles', function (Blueprint $table) {

            $table->id();

            $table->string('nom_modele');

            $table->unsignedBigInteger('marque_id');
            $table->foreign('marque_id')->references('id')->on('marques');

            $table->softDeletes();

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('modeles_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modeles');
    }
};
