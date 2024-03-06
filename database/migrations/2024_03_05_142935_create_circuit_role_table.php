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
        Schema::create('circuit_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            // $table->foreign('role_id')->references('id')->on('roles');

            $table->unsignedBigInteger('circuit_id');
            // $table->foreign('circuit_id')->references('id')->on('circuits');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circuit_role');
    }
};
