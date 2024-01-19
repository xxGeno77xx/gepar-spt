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
        Schema::table('engines', function (Blueprint $table) {
            $table->unsignedBigInteger('chauffeur_id')->nullable();
            $table->foreign('chauffeur_id')->references('id')->on('chauffeurs');
        });

        Schema::table('chauffeurs', function (Blueprint $table) {

                $table->unsignedBigInteger('engine_id')->nullable();
                $table->foreign('engine_id')->references('id')->on('engines');

        });

        Schema::table('reparations', function (Blueprint $table) {

            $table->unsignedBigInteger('prestataire_id')->nullable();
            $table->foreign('prestataire_id')->references('id')->on('prestataires');

    });

    //     Schema::table('reparations', function (Blueprint $table) {

    //         $table->unsignedBigInteger('type_reparation_id')->nullable();
    //         $table->foreign('type_reparation_id')->references('id')->on('reparations');

    // });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engines_and_chauffeurs_tables', function (Blueprint $table) {
            //
        });
    }
};
