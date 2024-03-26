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
        Schema::create('planning_voyages', function (Blueprint $table) {
            $table->id();

            $table->json('order');
            // $table->date('date_debut');
            // $table->date('date_fin');
            $table->boolean('exterieur');
            $table->timestamps();

            // $table->string('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_voyages');
    }
};
