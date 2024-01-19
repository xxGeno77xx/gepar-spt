<?php

use App\Support\Database\StatesClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::create('modeles', function (Blueprint $table) {
            
            $table->id();

            $table->string('nom_modele');

            $table->unsignedBigInteger('marque_id');
            $table->foreign('marque_id')->references('id')->on('marques');

            $table->softDeletes();

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value,]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modeles');
    }
};
