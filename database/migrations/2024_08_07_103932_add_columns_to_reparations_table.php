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
        Schema::table('reparations', function (Blueprint $table) {
            $table->string('exercice')->nullable();
            $table->string('budget')->nullable();
            $table->string('type_budget')->nullable();
            $table->string('num_budget')->nullable();
            $table->string('insc_budget')->nullable();
            $table->integer('compte_imputation')->nullable();
            $table->integer('dispo_prov')->nullable();
            $table->integer('montant_proj')->nullable();
            $table->integer('dispo_prov_apre')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reparations', function (Blueprint $table) {
            //
        });
    }
};
