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
        Schema::create('visites', function (Blueprint $table) {
            $table->id();

            $table->date('date_initiale');

            $table->date('date_expiration');

            $table->unsignedBigInteger('engine_id');
            $table->foreign('engine_id')->references('id')->on('engines');

            $table->enum('state', [StatesClass::Activated()->value, StatesClass::Deactivated()->value, StatesClass::Suspended()->value,]);

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
        Schema::dropIfExists('visites');
    }
};
