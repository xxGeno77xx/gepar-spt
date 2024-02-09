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
        Schema::dropIfExists('assurances');

        Schema::create('assurances', function (Blueprint $table) {
            $table->id();

            $table->date('date_debut');

            $table->date('date_fin');

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            $table->softDeletes();

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('updated_at_user_id');

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('assurances_id_seq');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assurances');
    }
};
