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
        Schema::create('affectation_chauffeurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chauffeur_id');
            $table->unsignedBigInteger('old_engine_id')->nullable();
            $table->unsignedBigInteger('new_engine_id');
            $table->date('date_affectation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectation_chauffeurs');
    }
};
