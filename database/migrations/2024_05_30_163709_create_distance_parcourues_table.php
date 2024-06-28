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
        Schema::create('distance_parcourues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('engine_id');
            $table->integer('distance');
            $table->date('date_distance_parcourue');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distance_parcourues');
    }
};
