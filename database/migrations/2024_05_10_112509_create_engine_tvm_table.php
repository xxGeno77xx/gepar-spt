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
        Schema::create('engine_tvm', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('engine_id');

            $table->unsignedBigInteger('tvm_id');

            $table->integer('montant');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engine_tvm');
    }
};
