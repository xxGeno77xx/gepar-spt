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
        
        Schema::create('engines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('modele_id');
            $table->foreign('modele_id')->references('id')->on('modeles');
            
            $table->integer('power');

            $table->unsignedBigInteger('departement_id')->nullable();
            $table->foreign('departement_id')->references('id')->on('departements');
           
            $table->string('price')->nullable();

            $table->date('circularization_date')->nullable();

            $table->date('date_aquisition')->nullable();

            $table->string('plate_number')->unique();

            
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('types');

            $table->string('car_document')->nullable();

            $table->unsignedBigInteger('carburant_id');
            $table->foreign('carburant_id')->references('id')->on('carburants');

            $table->boolean('assurances_mail_sent')->comment('bool to check if assurance mail was sent for a given engine');

            $table->boolean('visites_mail_sent')->comment('bool to check if visite mail was sent for a given engine 1=true= mail was sent; 0=false= mail not sent');

            $table->enum('state', [StatesClass::Activated()->value,
             StatesClass::Deactivated()->value,
            StatesClass::Suspended()->value,
            StatesClass::Repairing()->value
            ]);


            $table->string('numero_chassis')
            ->unique();

            $table->integer('moteur');

            $table->string('carosserie');


            $table->integer('pl_ass')->nullable();

            $table->string('matricule_precedent')
            ->unique()
            ->nullable();

            $table->integer('poids_total_en_charge');

            $table->integer('poids_a_vide');

            $table->integer('poids_total_roulant')->nullable();

            $table->integer('Charge_utile');

            $table->double('largeur');

            $table->double('surface');

            $table->string('couleur');

            $table->string('date_cert_precedent')->nullable();

            $table->string('kilometrage_achat')->nullable();

            $table->string('numero_carte_grise')
            ->unique()
            ->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('updated_at_user_id');

            $table->softDeletes();
            
            $table->timestamps();
        
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