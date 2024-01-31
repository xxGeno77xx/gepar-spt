<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('consommation_carburants', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('quantite');
        //     $table->dateTime('date');

        //     $table->unsignedBigInteger('engine_id');
        //     $table->foreign('engine_id')->references('id')->on('engines');

        //     $table->unsignedBigInteger('carburant_id');
        //     $table->foreign('carburant_id')->references('id')->on('carburants');

        //     $table->integer('kilometres_a_remplissage');

        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consommation_carburants');
    }
};
