<?php

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
        Schema::create('ordre_de_missions', function (Blueprint $table) {
            $table->id();

            $table->integer('numero_ordre');

            $table->unsignedBigInteger('chauffeur_id');
            $table->foreign('chauffeur_id')->references('id')->on('chauffeurs');

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            $table->json('lieu'); //lieu de la mission

            $table->json('agents');

            $table->string('initials');

            $table->string('objet_mission');

            $table->string('departement_id');

            $table->boolean('is_ordre_de_route')->nullable();

            $table->date('date_de_depart');

            $table->date('date_de_retour');

            $table->string('charge')->nullable(); //preneur en charge de la mission

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordre_de_missions');
    }
};
