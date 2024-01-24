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
        Schema::create('reparation_type_reparation', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('reparation_id')->nullable();
            $table->foreign('reparation_id')->references('id')->on('reparations');

            $table->unsignedBigInteger('type_reparation_id')->nullable();
            $table->foreign('type_reparation_id')->references('id')->on('type_reparations');

            $table->enum('state',[
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
            ]);
 

            $table->timestamps();
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
