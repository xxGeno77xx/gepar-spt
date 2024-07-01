<?php

use App\Support\Database\ChauffeursStatesClass;
use App\Support\Database\StatesClass;
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

        // Schema::dropIfExists('chauffeurs');

        Schema::create('chauffeurs', function (Blueprint $table) {
            $table->id();

            $table->string('fullname');

            $table->unsignedBigInteger('engine_id')->nullable();

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
            ])->default(StatesClass::Activated()->value);

            $table->enum('mission_state', [

                ChauffeursStatesClass::Disponible()->value,
                ChauffeursStatesClass::En_mission()->value,
                ChauffeursStatesClass::Programme()->value,

            ]);

            // $table->unsignedBigInteger('departement_id');

            // $table->foreign('departement_id')->references('id')->on('departements');

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('chauffeurs_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chauffeurs');
    }
};
