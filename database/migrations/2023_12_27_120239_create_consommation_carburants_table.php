<?php

use App\Support\Database\StatesClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     protected $connection = 'oracle';

    public function up(): void
    {
        Schema::dropIfExists('consommation_carburants');

        Schema::create('consommation_carburants', function (Blueprint $table) {
            $table->id();

            $table->integer('quantite');

            $table->dateTime('date');

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            $table->unsignedBigInteger('carburant_id');
            $table->foreign('carburant_id')->references('id')->on('carburants');

            $table->unsignedBigInteger('chauffeur_id');
            $table->foreign('chauffeur_id')->references('id')->on('chauffeurs');

            $table->string('carte_recharge_id');

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);


            $table->string('ticket');

            $table->string('observation')->nullable();

            $table->integer('kilometres_a_remplissage');

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('consommation_carburants_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consommation_carburants');
    }
};
