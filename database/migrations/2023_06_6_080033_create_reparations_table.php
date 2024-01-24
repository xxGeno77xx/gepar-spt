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
        Schema::create('reparations', function (Blueprint $table) {

            $table->id();

            $table->date('date_lancement');

            $table->date('date_fin')->nullable();

            $table->string('facture')->nullable();

            $table->text('details')->nullable();

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            // $table->unsignedBigInteger('prestataire_id');
            // $table->foreign('prestataire_id')->references('id')->on('fournisseurs');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('updated_at_user_id');

            $table->json('infos')->nullable();
            
            $table->integer('cout_reparation')->nullable();

            $table->enum('state', [StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
                ]);
            
            $table->timestamps();

            $table->softDeletes();
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
