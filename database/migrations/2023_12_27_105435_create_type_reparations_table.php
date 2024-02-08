<?php

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
        Schema::dropIfExists('type_reparations');

        Schema::create('type_reparations', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
                StatesClass::Repairing()->value,
            ]);
            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('type_reparations_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_reparations');
    }
};
