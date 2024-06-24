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

        // Schema::dropIfExists('types_engins');

        Schema::create('types_engins', function (Blueprint $table) {
            $table->id();

            $table->string('nom_type');

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);

            $table->softDeletes();

            $table->timestamps();

            $sequence = DB::getSequence();
            $sequence->drop('types_engins_id_seq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_engins');
    }
};
