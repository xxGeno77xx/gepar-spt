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
        Schema::dropIfExists('reparation_type_reparation');

        Schema::create('reparation_type_reparation', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reparation_id')->nullable();
            $table->foreign('reparation_id')->references('id')->on('reparations');

            $table->unsignedBigInteger('type_reparation_id')->nullable();
            $table->foreign('type_reparation_id')->references('id')->on('type_reparations');

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('reparation_type_reparation_id_');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparation_type_reparation');
    }
};
