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
        // Schema::dropIfExists('consommation_carburants');

        Schema::create('consommation_carburants', function (Blueprint $table) {
            $table->id();

            $table->integer('quantite');

            $table->integer('prix_unitaire');

            $table->integer('montant_total');

            $table->date('date_prise');

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')
                ->references('id')
                ->on('engines');

            $table->unsignedBigInteger('carburant_id')
                ->nullable();
            $table->foreign('carburant_id')
                ->references('id')
                ->on('carburants');

            $table->unsignedBigInteger('chauffeur_id')->nullable();
            $table->foreign('chauffeur_id')
                ->references('id')
                ->on('chauffeurs');

            $table->string('carte_recharge_id')
                ->nullable();

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);

            $table->string('ticket')
                ->nullable();

            $table->string('conducteur')
                ->nullable();

            $table->string('observation')
                ->nullable();

            $table->integer('kilometres_a_remplissage');

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('consommation_carburants_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consommation_carburants');
    }
};
