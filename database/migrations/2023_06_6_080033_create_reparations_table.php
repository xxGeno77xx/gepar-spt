<?php

use App\Support\Database\AppreciationClass;
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
        Schema::dropIfExists('reparations');

        Schema::create('reparations', function (Blueprint $table) {

            $table->id();

            $table->date('date_lancement');

            $table->date('date_fin')->nullable();

            $table->string('facture')->nullable();

            $table->text('details')->nullable();

            $table->text('rapport_final')->nullable();

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            $table->unsignedBigInteger('prestataire_id')->nullable();
            // $table->foreign('prestataire_id')->references('id')->on('fournisseurs');

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('updated_at_user_id');

            $table->json('infos')->nullable();

            $table->integer('cout_reparation')->nullable();

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
            ]);

            $table->integer('validation_step');  //   validation roles are stored in array. validation step is the said array key

            $table->string('validation_state');  //role in validation circuit

            $table->string('bon_commande')->nullable();

            $table->string('motif_rejet')->nullable();

            $table->string('avis_dg')->nullable();

            $table->string('avis_diga')->nullable();

            $table->unsignedBigInteger('rejete_par')->nullable();

            $table->string('ref_proforma')->nullable();

            $table->string('appreciation')
                ->nullable()
                ->enum([

                    AppreciationClass::Satisfaisant()->value,
                    AppreciationClass::Insatisfaisant()->value,
                ]);

            $table->unsignedBigInteger('circuit_id')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $sequence = DB::getSequence();
            $sequence->drop('reparations_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparations');
    }
};
