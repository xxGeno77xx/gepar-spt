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

        // Schema::dropIfExists('engines');

        Schema::create('engines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('marque_id');
            $table->foreign('marque_id')->references('id')->on('marques');

            $table->integer('power')->nullable();

            $table->unsignedBigInteger('departement_id')->nullable();
            // $table->foreign('departement_id')->references('code_centre')->on('CENTRE');

            $table->string('price')->nullable();

            $table->date('circularization_date')->nullable();

            $table->date('date_aquisition')->nullable();

            $table->string('plate_number')->unique();

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('types_engins');

            $table->string('car_document')->nullable();

            $table->unsignedBigInteger('carburant_id');
            $table->foreign('carburant_id')->references('id')->on('carburants');

            $table->boolean('assurances_mail_sent')->nullable();

            $table->boolean('tvm_mail_sent')->nullable();

            $table->boolean('visites_mail_sent')->nullable();

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
                StatesClass::Repairing()->value,
            ]);

            $table->string('numero_chassis')
                ->unique()
                ->nullable();

            $table->string('moteur')->nullable();

            $table->integer('pl_ass')->nullable();

            $table->string('matricule_precedent')
                ->unique()
                ->nullable();

            $table->double('poids_total_en_charge', 10, 2)->nullable();

            $table->double('poids_a_vide', 10, 2)->nullable();

            $table->double('poids_total_roulant', 10, 2)->nullable();

            $table->double('charge_utile', 10, 2)->nullable();

            $table->double('largeur', 10, 2)->nullable();

            $table->double('surface', 10, 2)->nullable();

            $table->string('couleur')->nullable();

            $table->string('date_cert_precedent')->nullable();

            $table->integer('kilometrage_achat')->nullable();

            $table->integer('distance_parcourue')->nullable();

            $table->string('numero_carte_grise')
                ->unique()
                ->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->integer('remainder')->default(0)->nullable();

            $table->unsignedBigInteger('updated_at_user_id')->nullable();

            $table->softDeletes();

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('engines_id_seq');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engines');
    }
};
