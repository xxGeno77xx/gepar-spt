<?php

use App\Support\Database\StatesClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('type_reparations', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');

            $table->enum('state', [
                StatesClass::Activated()->value,
                StatesClass::Deactivated()->value,
                StatesClass::Suspended()->value,
                StatesClass::Repairing()->value
            ]);
            $table->timestamps();
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
