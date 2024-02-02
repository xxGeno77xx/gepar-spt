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
    public function up(): void
    {
        Schema::create('chauffeurs', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('prenom');

            $table->string('age');

            $table->string('carte_identite');

            $table->string('num_permis');

            $table->string('permmis')
            ->comment('scan driver\'s license')->nullable();

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
            ])->default( StatesClass::Activated()->value);

            // $table->unsignedBigInteger('departement_id');

            // $table->foreign('departement_id')->references('id')->on('departements');

            $table->timestamps();
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
