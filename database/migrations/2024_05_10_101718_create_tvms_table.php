<?php

use App\Support\Database\StatesClass;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    protected $connection = 'oracle';

    public function up(): void
    {
        Schema::create('tvms', function (Blueprint $table) {
            $table->id();

            $table->date('date_debut');

            $table->date('date_fin');

            $table->string('reference');

            $table->unsignedBigInteger("engine_id");

            $table->integer("prix");

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('updated_at_user_id');

            $table->timestamps();

            $table->softDeletes();

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvms');
    }
};
